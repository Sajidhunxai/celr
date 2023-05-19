(function ($) {
    var WidgetDyncontel_ACFRepeaterHandler = function ($scope, $) {
        var elementSettings = dceGetElementSettings($scope);
        var $block_acfgallery = '.dce-acf-repeater';


        if (elementSettings.dce_acf_repeater_format == 'accordion') {
            $scope.find('.elementor-tab-title').on('click', function(){
                if (elementSettings.dce_acf_repeater_accordion_close) {
                    $scope.find('.elementor-active').not(this).each(function() {
                        jQuery(this).toggleClass('elementor-active');
                        jQuery(this).next().slideToggle().toggleClass('elementor-active');
                    });
                }
                jQuery(this).toggleClass('elementor-active');
                jQuery(this).next().slideToggle().toggleClass('elementor-active');
                return false;
            });
        }

        if (elementSettings.dce_acf_repeater_format == 'masonry') {

            var $grid_dce_repeater = $scope.find($block_acfgallery).masonry({
                // options
                itemSelector: '.dce-acf-repeater-item',
            });
            // ---------- [ imagesLoaded ] ---------
            $grid_dce_repeater.imagesLoaded().progress(function () {
                $grid_dce_repeater.masonry('layout');
            });
        } else if (elementSettings.dce_acf_repeater_format == 'justified') {
            $scope.find('.justified-grid').imagesLoaded().progress(function () {

            });

            $scope.find('.justified-grid').justifiedGallery({
                rowHeight: Number(elementSettings.justified_rowHeight.size) || 170,
                maxRowHeight: -1,
                selector: 'figure, div:not(.spinner)',
                imgSelector: '> img, > a > img, > div > a > img, > div > img',
                margins: Number(elementSettings.justified_margin.size) || 0,
                lastRow: elementSettings.justified_lastRow
            });


        } else if (elementSettings.dce_acf_repeater_format == 'slider_carousel') {

            var elementSwiper = $scope.find('.dce-acf-repeater-slider_carousel')[0];

            var id_scope = $scope.attr('data-id');
            var id_post = $scope.closest('.elementor').attr('data-post-id');
            var counter_id = $scope.find('.dce-acf-repeater-slider_carousel').attr('counter-id');

            var centroDiapo = false;
            var cicloInfinito = false;
            var slideInitNum = 0;
            var slidesPerView = Number(elementSettings.slidesPerView);

            var slideNum = $scope.find('.repeater-item').length;

            centerDiapo = Boolean(elementSettings.centeredSlides);
            cicloInfinito = Boolean(elementSettings.loop);

            var elementorBreakpoints = elementorFrontend.config.breakpoints;

            var swiperOptions = {
                // Optional parameters
                direction: 'horizontal', //String(elementSettings.direction_slider) || 'horizontal', //vertical

                initialSlide: slideInitNum,

                speed: Number(elementSettings.speed_slider) || 300,
                // setWrapperSize: false, // Enabled this option and plugin will set width/height on swiper wrapper equal to total size of all slides. It should be used mostly as a compatibility fallback option for browser that don’t support flexbox layouts well
                // virtualTranslate: false, // Enabled this option and swiper will be operated as usual except it will not move, real translate values on wrapper will not be set. Useful when you may need to create custom slide transition
                autoHeight: Boolean(elementSettings.autoHeight), //false, // Set to true and slider wrapper will adopt its height to the height of the currently active slide
                roundLengths: Boolean(elementSettings.roundLengths), //false, // Set to true to round values of slides width and height to prevent blurry texts on usual resolution screens (if you have such)
                // nested : Boolean( elementSettings.nested ), //false, // Set to true on nested Swiper for correct touch events interception. Use only on nested swipers that use same direction as the parent one
                // uniqueNavElements: true, // If enabled (by default) and navigation elements' parameters passed as a string (like ".pagination") then Swiper will look for such elements through child elements first. Applies for pagination, prev/next buttons and scrollbar elements
                //
                //effect: 'cube', //"slide", "fade", "cube", "coverflow" or "flip"
                effect: elementSettings.effects || 'slide',
                /*cubeEffect: {
                 shadow: true,
                 slideShadows: true,
                 shadowOffset: 20,
                 shadowScale: 0.94,
                 },*/
                /*coverflowEffect: {
                 rotate: 50,
                 stretch: 0,
                 depth: 100,
                 modifier: 1,
                 slideShadows : true,
                 },*/
                /*flipEffect: {
                 rotate: 30,
                 //slideShadows: true, //   Enables slides shadows
                 //limitRotation: true, //  Limit edge slides rotation
                 },*/

                // PARALLAX (è da implementare)
                //paralax: true,

                // LAZY-LOADING (è da implementare)
                //lazy: true,
                /*lazy {
                 loadPrevNext: false, //    Set to "true" to enable lazy loading for the closest slides images (for previous and next slide images)
                 loadPrevNextAmount: 1, //  Amount of next/prev slides to preload lazy images in. Can't be less than slidesPerView
                 loadOnTransitionStart: false, //   By default, Swiper will load lazy images after transition to this slide, so you may enable this parameter if you need it to start loading of new image in the beginning of transition
                 elementClass: 'swiper-lazy', //    CSS class name of lazy element
                 loadingClass: 'swiper-lazy-loading', //    CSS class name of lazy loading element
                 loadedClass: 'swiper-lazy-loaded', //  CSS class name of lazy loaded element
                 preloaderClass: 'swiper-lazy-preloader', //    CSS class name of lazy preloader
                 },*/

                // ZOOM (è da implementare)
                /*zoom {
                 maxRatio:  3, // Maximum image zoom multiplier
                 minRatio: 1, //    Minimal image zoom multiplier
                 toggle: true, //   Enable/disable zoom-in by slide's double tap
                 containerClass:    'swiper-zoom-container', // CSS class name of zoom container
                 zoomedSlideClass: 'swiper-slide-zoomed' // CSS class name of zoomed in container
                 },*/
                //slidesPerView: 'auto',
                slidesPerView: slidesPerView || 'auto',
                slidesPerGroup: Number(elementSettings.slidesPerGroup) || 1, // Set numbers of slides to define and enable group sliding. Useful to use with slidesPerView > 1


                spaceBetween: Number(elementSettings.spaceBetween) || 0, // 30,
                // ----------------------------
                slidesOffsetBefore: 0, //   Add (in px) additional slide offset in the beginning of the container (before all slides)
                slidesOffsetAfter: 0, //    Add (in px) additional slide offset in the end of the container (after all slides)

                slidesPerColumn: Number(elementSettings.slidesColumn) || 1, // 1, // Number of slides per column, for multirow layout
                slidesPerColumnFill: 'row', // Could be 'column' or 'row'. Defines how slides should fill rows, by column or by row

                centerInsufficientSlides: true,
                watchOverflow: true,
                centeredSlides: centroDiapo,

                grabCursor: Boolean(elementSettings.grabCursor), //true,

                //------------------- Freemode
                freeMode: Boolean(elementSettings.freeMode),
                freeModeMomentum: Boolean(elementSettings.freeModeMomentum),
                freeModeMomentumRatio: Number(elementSettings.freeModeMomentumRatio) || 1,
                freeModeMomentumVelocityRatio: Number(elementSettings.freeModeMomentumVelocityRatio) || 1,
                freeModeMomentumBounce: Boolean(elementSettings.freeModeMomentumBounce),
                freeModeMomentumBounceRatio: Number(elementSettings.speed) || 1,
                freeModeMinimumVelocity: Number(elementSettings.speed) || 0.02,
                freeModeSticky: Boolean(elementSettings.freeModeSticky),

                loop: cicloInfinito, // true,
                //loopFillGroupWithBlank: true,

                // ----------------------------
                // HASH (è da implementare)
                /*hashNavigation: {
                 //watchState   //default: false    Set to true to enable also navigation through slides (when hashnav is enabled) by browser history or by setting directly hash on document location
                 replaceState: true,    // default: false //    Works in addition to hashnav to replace current url state with the new one instead of adding it to history
                 },*/
                // HISTORY (è da implementare)
                //history: false,
                /*history: {
                 replaceState: false, //    Works in addition to hashnav or history to replace current url state with the new one instead of adding it to history
                 key: 'slides' //   Url key for slides
                 },*/
                // CONTROLLER (è da implementare)
                //controller: false,
                /*controller: {
                 control:   [Swiper Instance]   undefined   Pass here another Swiper instance or array with Swiper instances that should be controlled by this Swiper
                 inverse: false, // Set to true and controlling will be in inverse direction
                 by: 'slide', // Can be 'slide' or 'container'. Defines a way how to control another slider: slide by slide (with respect to other slider's grid) or depending on all slides/container (depending on total slider percentage)
                 },*/

                navigation: {
                    nextEl: id_post ? '.dce-elementor-post-'+id_post+' .elementor-element-' + id_scope + ' .next-' + counter_id : '.next-' + counter_id,
                    prevEl: id_post ? '.dce-elementor-post-'+id_post+' .elementor-element-' + id_scope + ' .prev-' + counter_id : '.prev-' + counter_id,
                },
                pagination: {
                    el: id_post ? '.dce-elementor-post-'+id_post+' .elementor-element-' + id_scope + ' .pagination-' + counter_id : '.pagination-' + counter_id, //'.swiper-pagination', //'.pagination-acfslider-'+id_scope,
                    clickable: true,
                    type: String(elementSettings.pagination_type) || 'bullets',
                    dynamicBullets: true,
                    renderFraction: function (currentClass, totalClass) {
                        return '<span class="' + currentClass + '"></span>' +
                                '<span class="separator">' + String(elementSettings.fraction_separator) + '</span>' +
                                '<span class="' + totalClass + '"></span>';
                    },
                },
                mousewheel: Boolean(elementSettings.mousewheelControl),
                keyboard: {
                    enabled: Boolean(elementSettings.keyboardControl),
                },

                on: {
                    init: function () {
                        $('body').attr('data-carousel-' + id_scope, this.realIndex);
                    },
                    slideChange: function (e) {
                        $('body').attr('data-carousel-' + id_scope, this.realIndex);
                    },
                }
            };
            if (elementSettings.useAutoplay) {

                //default
                swiperOptions = $.extend(swiperOptions, {autoplay: true});

                var autoplayDelay = Number(elementSettings.autoplay);
                if (!autoplayDelay) {
                    autoplayDelay = 3000;
                } else {
                    autoplayDelay = Number(elementSettings.autoplay);
                }
                swiperOptions = $.extend(swiperOptions, {autoplay: {delay: autoplayDelay, disableOnInteraction: Boolean(elementSettings.autoplayDisableOnInteraction), stopOnLastSlide: Boolean(elementSettings.autoplayStopOnLast)}});

            }

            //------------------- Responsive Params
            var spaceBetween = 0;
            if (elementSettings.spaceBetween) {
                spaceBetween = elementSettings.spaceBetween;
            }
            var responsivePoints = swiperOptions.breakpoints = {};
            responsivePoints[elementorBreakpoints.lg] = {
                slidesPerView: Number(elementSettings.slidesPerView) || 'auto',
                slidesPerGroup: Number(elementSettings.slidesPerGroup) || 1,
                spaceBetween: Number(spaceBetween) || 0,
                slidesPerColumn: Number(elementSettings.slidesColumn) || 1,
            };

            var spaceBetween_tablet = spaceBetween;
            if (elementSettings.spaceBetween_tablet) {
                spaceBetween_tablet = elementSettings.spaceBetween_tablet;
            }
            responsivePoints[elementorBreakpoints.md] = {
                slidesPerView: Number(elementSettings.slidesPerView_tablet) || Number(elementSettings.slidesPerView) || 'auto',
                slidesPerGroup: Number(elementSettings.slidesPerGroup_tablet) || Number(elementSettings.slidesPerGroup) || 1,
                spaceBetween: Number(spaceBetween_tablet) || 0,
                slidesPerColumn: Number(elementSettings.slidesColumn_tablet) || Number(elementSettings.slidesColumn) || 1,
            };

            var spaceBetween_mobile = spaceBetween_tablet;
            if (elementSettings.spaceBetween_mobile) {
                spaceBetween_mobile = elementSettings.spaceBetween_mobile;
            }
            responsivePoints[elementorBreakpoints.xs] = {
                slidesPerView: Number(elementSettings.slidesPerView_mobile) || Number(elementSettings.slidesPerView_tablet) || Number(elementSettings.slidesPerView) || 'auto',
                slidesPerGroup: Number(elementSettings.slidesPerGroup_mobile) || Number(elementSettings.slidesPerGroup_tablet) || Number(elementSettings.slidesPerGroup) || 1,
                spaceBetween: Number(spaceBetween_mobile) || 0,
                slidesPerColumn: Number(elementSettings.slidesColumn_mobile) || Number(elementSettings.slidesColumn_tablet) || Number(elementSettings.slidesColumn) || 1,
            };
            swiperOptions = $.extend(swiperOptions, responsivePoints);

            if ( 'undefined' === typeof Swiper ) {
              const asyncSwiper = elementorFrontend.utils.swiper;

              new asyncSwiper( jQuery( elementSwiper ), swiperOptions ).then( ( newSwiperInstance ) => {
                mySwiper = newSwiperInstance;
              } );
            } else {
              mySwiper = new Swiper( jQuery( elementSwiper ), swiperOptions );
            }

            if (elementSettings.useAutoplay && elementSettings.autoplayStopOnHover) {
                $(elementSwiper).on({
                    mouseenter: function () {
                        mySwiper.autoplay.stop();
                    },
                    mouseleave: function () {
                        mySwiper.autoplay.start();
                    }
                });
            }

        } // end if SliderCarousel

        // ======================================================================================
        if (elementSettings.enabled_wow) {
            var wow = new WOW(
                    {
                        boxClass: 'wow', // animated element css class (default is wow)
                        animateClass: 'animated', // animation css class (default is animated)
                        offset: 0, // distance to the element when triggering the animation (default is 0)
                        mobile: true, // trigger animations on mobile devices (default is true)
                        live: true, // act on asynchronously loaded content (default is true)
                        callback: function (box) {
                            // the callback is fired every time an animation is started
                            // the argument that is passed in is the DOM node being animated
                        },
                        scrollContainer: null // optional scroll container selector, otherwise use window
                    }
            );
            wow.init();
        }

        // PHOTO SWIPE

        var initPhotoSwipeFromDOM = function (gallerySelector) {
            // parse slide data (url, title, size ...) from DOM elements
            // (children of gallerySelector)
            var parseThumbnailElements = function (el) {
                var thumbElements = el.childNodes,
                        numNodes = thumbElements.length,
                        items = [],
                        figureEl,
                        linkEl,
                        size,
                        item;

                for (var i = 0; i < numNodes; i++) {
                    figureEl = thumbElements[i]; // <figure> element

                    // include only element nodes
                    if (figureEl.nodeType !== 1) {
                        continue;
                    }

                    linkEl = figureEl.children[0].getElementsByTagName('a')[0]; // <a> element

                    size = linkEl.getAttribute('data-size').split('x');

                    // create slide object
                    item = {
                        src: linkEl.getAttribute('href'),
                        w: parseInt(size[0], 10),
                        h: parseInt(size[1], 10)
                    };



                    if (figureEl.children.length > 1) {
                        // <figcaption> content
                        item.title = figureEl.children[1].innerHTML;
                    }

                    if (linkEl.children.length > 0) {
                        // <img> thumbnail element, retrieving thumbnail url
                        item.msrc = linkEl.children[0].getAttribute('src');
                    }

                    item.el = figureEl; // save link to element for getThumbBoundsFn
                    items.push(item);
                }

                return items;
            };

            // find nearest parent element
            var closest = function closest(el, fn) {
                return el && (fn(el) ? el : closest(el.parentNode, fn));
            };

            // triggers when user clicks on thumbnail
            var onThumbnailsClick = function (e) {
                e = e || window.event;
                e.preventDefault ? e.preventDefault() : e.returnValue = false;

                var eTarget = e.target || e.srcElement;

                // find root element of slide
                var clickedListItem = closest(eTarget, function (el) {
                    return (el.tagName && el.tagName.toUpperCase() === 'FIGURE');
                });

                if (!clickedListItem) {
                    return;
                }

                // find index of clicked item by looping through all child nodes
                // alternatively, you may define index via data- attribute
                var clickedGallery = clickedListItem.parentNode,
                        childNodes = clickedListItem.parentNode.childNodes,
                        numChildNodes = childNodes.length,
                        nodeIndex = 0,
                        index;

                for (var i = 0; i < numChildNodes; i++) {
                    if (childNodes[i].nodeType !== 1) {
                        continue;
                    }

                    if (childNodes[i] === clickedListItem) {
                        index = nodeIndex;
                        break;
                    }
                    nodeIndex++;
                }



                if (index >= 0) {
                    // open PhotoSwipe if valid index found
                    openPhotoSwipe(index, clickedGallery);
                }
                return false;
            };

            // parse picture index and gallery index from URL (#&pid=1&gid=2)
            var photoswipeParseHash = function () {
                var hash = window.location.hash.substring(1),
                        params = {};

                if (hash.length < 5) {
                    return params;
                }

                var vars = hash.split('&');
                for (var i = 0; i < vars.length; i++) {
                    if (!vars[i]) {
                        continue;
                    }
                    var pair = vars[i].split('=');
                    if (pair.length < 2) {
                        continue;
                    }
                    params[pair[0]] = pair[1];
                }

                if (params.gid) {
                    params.gid = parseInt(params.gid, 10);
                }

                return params;
            };

            var openPhotoSwipe = function (index, galleryElement, disableAnimation, fromURL) {
                var pswpElement = document.querySelectorAll('.pswp')[0],
                        gallery,
                        options,
                        items;

                items = parseThumbnailElements(galleryElement);

                // define options (if needed)
                options = {

                    // define gallery index (for URL)
                    galleryUID: galleryElement.getAttribute('data-pswp-uid'),

                    getThumbBoundsFn: function (index) {
                        // See Options -> getThumbBoundsFn section of documentation for more info
                        var thumbnail = items[index].el.getElementsByTagName('img')[0], // find thumbnail
                                pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                                rect = thumbnail.getBoundingClientRect();

                        return {x: rect.left, y: rect.top + pageYScroll, w: rect.width};
                    }

                };

                // PhotoSwipe opened from URL
                if (fromURL) {
                    if (options.galleryPIDs) {
                        // parse real index when custom PIDs are used
                        // http://photoswipe.com/documentation/faq.html#custom-pid-in-url
                        for (var j = 0; j < items.length; j++) {
                            if (items[j].pid == index) {
                                options.index = j;
                                break;
                            }
                        }
                    } else {
                        // in URL indexes start from 1
                        options.index = parseInt(index, 10) - 1;
                    }
                } else {
                    options.index = parseInt(index, 10);
                }

                // exit if index not found
                if (isNaN(options.index)) {
                    return;
                }

                if (disableAnimation) {
                    options.showAnimationDuration = 0;
                }

                // Pass data to PhotoSwipe and initialize it
                gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
                gallery.init();
            };

            // loop through all gallery elements and bind events
            var galleryElements = document.querySelectorAll(gallerySelector);

            for (var i = 0, l = galleryElements.length; i < l; i++) {
                galleryElements[i].setAttribute('data-pswp-uid', i + 1);
                galleryElements[i].onclick = onThumbnailsClick;
            }

            // Parse URL and open gallery if it contains #&pid=3&gid=1
            var hashData = photoswipeParseHash();
            if (hashData.pid && hashData.gid) {
                openPhotoSwipe(hashData.pid, galleryElements[ hashData.gid - 1 ], true, true);
            }
        };
        //
        if ($scope.find('.dynamic_acfgallery.is-lightbox.photoswipe, .dynamic_gallery.is-lightbox.photoswipe').length > 0) {
            //
            if ($('body').find('.pswp').length < 1)
                photoSwipeContent();

            initPhotoSwipeFromDOM('.dynamic_acfgallery.is-lightbox.photoswipe, .dynamic_gallery.is-lightbox.photoswipe');
        }
    };

    // Make sure you run this code under Elementor..
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/dyncontel-acf-repeater.default', WidgetDyncontel_ACFRepeaterHandler);
    });
    var photoSwipeContent = function () {
        $('body').append('<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true"><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div><div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter"></div><button class="pswp__button pswp__button--close" title="Close (Esc)"></button><button class="pswp__button pswp__button--share" title="Share"></button><button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button><button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button><button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>');
    };
})(jQuery);
