(function($) {
    "use strict";

    function testimonialSlider() {

        /*feedback__active***/
        let feedback__active = new Swiper(".feedback__active", {
            slidesPerView: 3,
            spaceBetween: 30,
            loop: true,
            centeredSlides: true,
            autoplay: {
                delay: 3000,
            },
            navigation: {
                prevEl: ".feedback__item__button-prev",
                nextEl: ".feedback__item__button-next",
            },
            breakpoints: {
                1200: {
                    slidesPerView: 3,
                },
                768: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        });

        /*customer-feedback__active***/
        let customerFeedback__active = new Swiper(".customer-feedback__active", {
            slidesPerView: 3,
            spaceBetween: 30,
            loop: true,
            centeredSlides: true,
            autoplay: {
                delay: 3000,
            },
            pagination: {
                el: ".customer-feedback__dot",
                clickable: true,
            },
            breakpoints: {
                1200: {
                    slidesPerView: 3,
                },
                768: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        });

        $("[data-background]").each(function() {
            $(this).css(
                "background-image",
                "url( " + $(this).attr("data-background") + "  )"
            );
        });
    }

    function projectSlider() {

        let caseStudies = new Swiper(".case-studies__active", {
            slidesPerView: 1,
            spaceBetween: 40,
            loop: false,
            roundLengths: true,
            clickable: true,
            scrollbar: {
                el: ".case-studies__scrollbar",
                hide: true,
            },
            autoplay: {
                delay: 3000,
            },
            breakpoints: {
                1200: {
                    slidesPerView: 3,
                },
                501: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        });

        var coolAmazing__slider = new Swiper(".cool-amazing__slider", {
            slidesPerView: 5,
            spaceBetween: 30,
            loop: true,
            centeredSlides: true,
            autoplay: {
                delay: 3000,
            },
            pagination: {
                el: ".cool-amazing__slider-dot",
                clickable: true,
            },
            breakpoints: {
                1200: {
                    slidesPerView: 5,
                },
                768: {
                    slidesPerView: 3,
                },
                0: {
                    slidesPerView: 2,
                },
            },
        });

        let recentWorksActive = new Swiper(".recent-works__active", {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: false,
            roundLengths: true,
            clickable: true,
            scrollbar: {
                el: ".recent-works__scrollbar",
                hide: true,
            },
            autoplay: {
                delay: 3000,
            },
            breakpoints: {
                1200: {
                    slidesPerView: 3,
                },
                611: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        });

    }

    function teamSlider() {

        let specialistsActive = new Swiper(".specialists__active", {
            slidesPerView: 1,
            spaceBetween: 40,
            loop: false,
            roundLengths: true,
            clickable: true,
            navigation: {
                prevEl: ".specialists__slider-arrow-prev",
                nextEl: ".specialists__slider-arrow-next",
            },
            scrollbar: {
                el: ".specialists__scrollbar",
                hide: true,
            },
            autoplay: {
                delay: 3000,
            },
            breakpoints: {
                1200: {
                    slidesPerView: 4,
                },
                992: {
                    slidesPerView: 3,
                },
                481: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        });

    }

    function quoteSlider() {

        $("[data-background]").each(function() {
            $(this).css(
                "background-image",
                "url( " + $(this).attr("data-background") + "  )"
            );
        });

        let decisionMakingActive = new Swiper(".decision-making__active", {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            centeredSlides: true,
            autoplay: {
                delay: 3000,
            },
            navigation: {
                prevEl: ".decision-making__slider-prev",
                nextEl: ".decision-making__slider-next",
            },
        });

    }

    function serviceSlider(){
        /*what-we-do__active***/
        let whatWeDoActive = new Swiper(".what-we-do__active", {
            slidesPerView: 1,
            spaceBetween: 40,
            loop: false,
            roundLengths: true,
            clickable: true,
            navigation: {
                prevEl: ".what-we-do__slider-arrow-prev",
                nextEl: ".what-we-do__slider-arrow-next",
            },
            scrollbar: {
                el: ".what-we-do__scrollbar",
                hide: true,
            },
            autoplay: {
                delay: 3000,
            },
            breakpoints: {
                1400: {
                    slidesPerView: 4,
                },
                1200: {
                    slidesPerView: 3,
                },
                768: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        });
    }

    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/weta_testimonial_slider.default', testimonialSlider );
        elementorFrontend.hooks.addAction('frontend/element_ready/weta_project_slider.default', projectSlider );
        elementorFrontend.hooks.addAction('frontend/element_ready/weta_team_slider.default', teamSlider );
        elementorFrontend.hooks.addAction('frontend/element_ready/weta_quote_slider.default', quoteSlider );
        elementorFrontend.hooks.addAction('frontend/element_ready/weta_services.default', serviceSlider );
    });

})(jQuery);