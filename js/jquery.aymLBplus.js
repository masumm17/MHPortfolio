if ( typeof Object.create !== 'function' ) {
    Object.create = function( obj ) {
        function F() {};
        F.prototype = obj;
        return new F();
    };
}

(function() {
    var matched, browser;
    jQuery.uaMatch = function( ua ) {
        ua = ua.toLowerCase();
        var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
            /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
            /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
            /(msie) ([\w.]+)/.exec( ua ) ||
            ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
            [];
        return {
            browser: match[ 1 ] || "",
            version: match[ 2 ] || "0"
        };
    };
    matched = jQuery.uaMatch( navigator.userAgent );
    browser = {};
    if ( matched.browser ) {
        browser[ matched.browser ] = true;
        browser.version = matched.version;
    }
    if ( browser.chrome ) {
        browser.webkit = true;
    } else if ( browser.webkit ) {
        browser.safari = true;
    }
    jQuery.browser = browser;
})();

(function( $, window, document ) {
    var aymGallery = {
        init: function(o,t){
            var self = this;
            self.options = $.extend( {}, $.fn.aymLBplus.options, o );
            self.smartZoom = typeof $.fn.smartZoom == "function" ;
            self.url = t.data('porfoliourl');
            self.smartZoomOptions = {
                top:0,
                left: 0,
                width: '100%',
                height: '100%',
                containerBackground: 'transparent',
                containerClass: 'aymLBZoomBox'
            }
            
            self.projects = $('.singleProject',t);
            if(self.projects.size()<1){
                return false;
            }
            self.activeProject = self.projects.eq(0);
            self.loadTemplate();
            self.projects.on('click','.ProjectThumb', function(e){
                e.preventDefault();
                $thisProject = $(this).closest('.singleProject');
                self.activeProject = $thisProject;
                self.activePhoto = self.activeProject.find('.ProjectPhoto a').eq(0);
                self.open($thisProject);
            });
        },
        loadTemplate: function(){
            var self = this;
            self.working = false;
            self.wrap = $('<div/>');
            self.wrap
                    .css('background-color', self.options.backgroundColor)
                    .addClass('aymLBWrap')
                    .append('<div class="aymClose"><div class="aymCloseInner"><div class="aymCloseTrig useSprite fadeHover"></div></div></div>')
                    .appendTo('body');
            if(self.options.showProjectsNav){
                self.projectsNav = $('<div class="aymTopNav aymLBControl"></div>');
                self.projectsNav
                        //.append('<div class="aymLBTbox">' + self.options.showProjectsNavText + '</div>')
                        .append('<div class="aymLBnavLeft"><div data-action="prev" class="aymLBnli pcontrol useSprite fadeHover"></div></div>')
                        .append('<div class="aymLBTnbox">' + self.options.showProjectsNavText + ' ' + '<span class="currentProjectC">0</span> of ' + self.projects.size() +'</div>')
                        //.append('<div class="aymLBpanel"><div data-action="thumb" class="aymLBpanelI pcontrol useSprite fadeHover"></div></div>')
                        .append('<div class="aymLBnavRight"><div data-action="next" class="aymLBnri pcontrol useSprite fadeHover"></div></div>')
                        .appendTo(self.wrap)
                        .on('click', '.pcontrol', function(){
                            switch($(this).data('action')){
                                case 'next': self.nextProject();
                                             break;
                                case 'prev': self.prevProject();
                                             break;
                                case 'thumb': self.projectThumbs();
                                              break;
                                defaulte: break;
                            }
                        });
                        self.aprCon = $('.currentProjectC', self.wrap);
            }
            self.pdWrap = $('<div/>');
            self.pdWrap
                      .addClass('aymLBInfoBox')
                      .append('<div class="aymLBInfoBoxInner"><div class="aymLBItemDetails"></div>')
                      .appendTo(self.wrap);
            self.pdBox = $('.aymLBItemDetails');
            
            self.imgBox = $('<div class="aymLBImgBox"></div>');
            self.zoomBox = $('<div class="aymLBimgBoxZoom"></div>');
            self.imgBox
                    .append(self.zoomBox)
                    .appendTo(self.wrap);
            
            $(window).bind('resize', function(){
                if(!self.working){
                    return ;
                }
                self.zoomBox.smartZoom('destroy');
                self.zoomBox.smartZoom(self.smartZoomOptions);
            });

            self.imgLoading = '<img src="' + self.url + '/images/loading.gif"/>';
            
            if(self.options.showPhotosNav){
                self.photoThumbOW = $('<div></div>');
                self.photoThumbOW
                                  .append('<div class="aymLBthumbOverlay"></div>')
                                  .append('<div class="aymThumbBox"><div class="aymThubHead">All Photos <span class="photoNum"></span></div><div class="aymTclose"><div class="aymTcloseIcon useSprite fadeHover"></div></div><div class="aymThumbWrap"><div class="aymThumbsImg"></div></div></div>')
                                  .appendTo(self.wrap);
                self.photThumbs = $('.aymThumbsImg', self.photoThumbOW);
                self.photThumbs.on('click', 'img', function(e){
                    e.preventDefault();
                    if($(this).index() === self.activePhoto.index()){
                        self.hidePhotoThumbs();
                        return;
                    }
                    var ph = self.activeProject.find('.ProjectPhoto a').eq($(this).index());
                    
                    self.changePhoto(ph);
                    self.hidePhotoThumbs();
                });
                $('.aymTclose', self.photoThumbOW).on('click', function(e){
                    e.preventDefault();
                    self.hidePhotoThumbs();
                });
                
                self.photoNav = $('<div class="aymBottomNav aymLBControl"></div>');
                self.photoNav
                        .append('<div class="aymLBTbox">' + self.options.showPhotNavText + '</div>')
                        .append('<div class="aymLBnavLeft"><div data-action="prev" title="Previous photo of this project." class="aymLBnli pcontrol useSprite fadeHover"></div></div>')
                        .append('<div class="aymLBpanel"><div data-action="thumb" class="aymLBpanelI pcontrol useSprite fadeHover"></div></div>')
                        .append('<div class="aymLBnavRight"><div data-action="next" title="Next photo of this project." class="aymLBnri pcontrol useSprite fadeHover"></div></div>')
                        .append('<div class="aymLBTnbox">' + '<span class="currentPhotoP">0</span> of <span class="totalPhotoP">0</span></div>')
                        .appendTo(self.wrap)
                        .on('click', '.pcontrol', function(){
                            if(self.activeProject.find('.ProjectPhoto a').size()<=1 && $(this).data('action') != 'thumb'){
                                return false;
                            }
                            console.log($(this));
                            switch($(this).data('action')){
                                case 'next': self.nextPhoto();
                                             break;
                                case 'prev': self.prevPhoto();
                                             break;
                                case 'thumb': self.showPhotoThumbs();
                                              break;
                                defaulte: break;
                            }
                        });
                        self.aphCon = $('.currentPhotoP', self.wrap);
                        self.apTCon = $('.totalPhotoP', self.wrap);
            }
            
            $('.aymCloseTrig', self.wrap).on('click',function(e){
                e.preventDefault();
                self.close();
                self.working = false;
            });
            
        },
        open : function(ap){
            var self = this;
            if(self.options.showProjectsNav && self.projects.size() <=1 ){
                $('.aymLBnavLeft, .aymLBnavRight', self.projectsNav).remove();
            }
            self.changeProject(ap);
            self.wrap.fadeIn(600, 'easeInOutBounce');
            self.working = true;
        },
        close : function(){
            var self = this;
            self.reset();
            self.wrap.fadeOut(600, 'easeInOutBounce');
            self.zoomBox.smartZoom('destroy');
        },
        nextProject: function(){
            var self = this;
            var ap;
            if(self.activeProject.is(':last-child')){
                ap = self.activeProject.siblings().first();
            }else{
                ap = self.activeProject.next();
            }
            self.changeProject(ap);
        },
        prevProject: function(){
            var self = this;
            var ap;
            if(self.activeProject.is(':first-child')){
                ap = self.activeProject.siblings().last();
            }else{
                ap = self.activeProject.prev();
            }
            self.changeProject(ap);
        },
        projectThumbs: function(){
            
        },
        nextPhoto: function(){
            var self = this;
            var ap;
            if(self.activePhoto.is(':last-child')){
                ap = self.activePhoto.siblings().first();
            }else{
                ap = self.activePhoto.next();
            }
            self.changePhoto(ap);
        },
        prevPhoto: function(){
            var self = this;
            var ap;
            if(self.activePhoto.is(':first-child')){
                ap = self.activePhoto.siblings().last();
            }else{
                ap = self.activePhoto.prev();
            }
            self.changePhoto(ap);
        },
        showPhotoThumbs: function(){
            var self = this;
            self.photThumbs.html('');
            console.log(self.activeProject);
            self.activeProject.find('.ProjectPhoto img').each(function(ti){
                self.photThumbs.append($(this).clone());
            });
            $('.aymThumbBox',self.photoThumbOW).css('height','185px');
            $('.aymLBthumbOverlay',self.photoThumbOW).css('height','100%');
        },
        hidePhotoThumbs: function(){
            var self = this;
            $('.aymThumbBox',self.photoThumbOW).css('height',0);
            $('.aymLBthumbOverlay',self.photoThumbOW).css('height',0);
        },
        changeProject: function(cp){
            var self = this;
            var ai = $('<img/>');
            self.zoomBox.smartZoom('destroy');
            self.zoomBox.html(self.imgLoading);
            var curThumbs = cp.find('.ProjectPhoto a');
            if(curThumbs.size() <=1 ){
                $('.aymLBnavLeft, .aymLBnavRight',self.photoNav).addClass('aymp-disable');
            }else{
                $('.aymLBnavLeft, .aymLBnavRight',self.photoNav).removeClass('aymp-disable');
            }
            ai.attr('src', curThumbs.eq(0).attr('href'));
            ai.on('load', function(){
                if(cp.index() != self.activeProject.index()){
                    return false;
                }
                self.zoomBox.html('').append(ai);
                self.zoomBox.smartZoom(self.smartZoomOptions);
            });
            self.pdBox.html(cp.find('.ProjectsDesc').html());
            
            self.aprCon.html(cp.index() + 1);
            
            self.aphCon.html(1);
            self.apTCon.html(curThumbs.size());
            self.activeProject = cp;
            self.activePhoto = curThumbs.eq(0);
        },
        changePhoto: function(ph){
            var self = this;
            var ai = $('<img/>');
            self.zoomBox.smartZoom('destroy');
            self.zoomBox.find('img').fadeOut(200, function(){
                self.zoomBox .html(self.imgLoading);
                ai.attr('src', ph.attr('href'));
                ai.on('load', function(){
                if(ph.index() != self.activePhoto.index()){
                    return false;
                }
                self.zoomBox.html('').append(ai);
                if(self.options.panAndZoom && self.smartZoom){
                    self.zoomBox.smartZoom(self.smartZoomOptions);
                }
            });
            });
            
            self.aphCon.html(ph.index() + 1);
            self.activePhoto = ph;
        },
        reset: function(){
            var self = this;
            self.activeProject = false;
            self.activePhoto = 0;
        }
    }
    
    
    $.fn.aymLBplus = function( options ) {
        return this.each(function() {
            var gallery = Object.create( aymGallery );
            gallery.init( options, $(this) );
        });
    };

    $.fn.aymLBplus.options = {
        //Projects Navigation Controll
        'showProjectsNav'   : true,
        'showProjectsNavText': 'Project',
        //Photos of a project navigation controll
        'showPhotosNav'     : true,
        'showPhotNavText': 'Photos of this projects',
        'backgroundColor'   : '#191919',
        
        'keyboardNav'       : true,
        
        'panAndZoom'        : true
        
    };
})( jQuery, window, document );
