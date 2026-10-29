(function ($) {
    'use strict';

    window.tpgFixLazyLoad = function () {
        $('.rt-tpg-container').each(function () {
            // jetpack Lazy load
            $(this).find('img.jetpack-lazy-image:not(.jetpack-lazy-image--handled)').each(function () {
                $(this).addClass('jetpack-lazy-image--handled').removeAttr('srcset').removeAttr('data-lazy-src').attr('data-lazy-loaded', 1);
            });
            //
            $(this).find('img.lazyload').each(function () {
                var src = $(this).attr('data-src') || '';
                if (src) {
                    $(this).attr('src', src).removeClass('lazyload').addClass('lazyloaded');
                }
            });
        });
    };

    window.initTpg = function () {

        $('.rt-tpg-container').each(function () {
            var container = $(this),
                str = $(this).attr("data-layout"),
                IsotopeWrap = '',
                isIsotop = $(".rt-tpg-isotope", container),
                IsoButton = $(".rt-tpg-isotope-buttons", container),
                loader = container.find(".rt-content-loader"),
                IsoDropdownFilter = $("select.isotope-dropdown-filter", container);

            setTimeout(function (){
                loader.removeClass('tpg-pre-loader');
            }, 800);
            if (str) {
                var qsRegex,
                    buttonFilter;

                if (isIsotop.length) {
                    var IsoURL = isIsoUrlGrid(isIsotop),
                        IsoCount = IsoButton.attr('data-count');

                    if (!buttonFilter) {
                        if (IsoButton.length) {
                            buttonFilter = IsoButton.find('button.selected').attr('data-filter');
                        } else if (IsoDropdownFilter.length) {
                            buttonFilter = IsoDropdownFilter.val();
                        }
                    }

                    container.trigger('tpg_item_before_load');
                    tpgFixLazyLoad();
                    IsotopeWrap = isIsotop.imagesLoaded(function () {
                        preFunction();
                        IsotopeWrap.isotope({
                            itemSelector: '.isotope-item',
                            masonry: {columnWidth: '.isotope-item'},
                            filter: function () {
                                var $this = $(this);
                                var searchResult = qsRegex ? $this.text().match(qsRegex) : true;
                                var buttonResult = buttonFilter ? isoItemMatches($this, buttonFilter) : true;
                                return searchResult && buttonResult;
                            }
                        });
                        setTimeout(function () {
                            IsotopeWrap.isotope();
                            container.trigger('tpg_item_after_load');
                        }, 100);
                    });

                    IsotopeWrap.on('arrangeComplete', function (event, filteredItems) {
                        if ($(".isotope-item", IsotopeWrap).is(":visible")) {
                            IsotopeWrap.parent().find('.isotope-term-no-post p').hide();
                        } else {
                            IsotopeWrap.parent().find('.isotope-term-no-post p').show();
                        }
                    });

                    // use value of search field to filter
                    var $quicksearch = container.find('.iso-search-input').keyup(debounce(function () {
                        qsRegex = new RegExp(escapeRegExp($quicksearch.val()), 'gi');
                        IsotopeWrap.isotope();
                    }));

                    IsoButton.on('click touchstart', 'button', function (e) {
                        e.preventDefault();
                        buttonFilter = $(this).attr('data-filter');
                        if (IsoURL) {
                            location.hash = "filter=" + encodeURIComponent(buttonFilter);
                        } else {
                            IsotopeWrap.isotope();
                            $(this).parent().find('.selected').removeClass('selected');
                            $(this).addClass('selected');
                        }
                    });
                    if (IsoURL) {
                        var applyHashFilter = function () {
                            buttonFilter = windowHashChange(IsoButton);
                            // Before init, the imagesLoaded callback applies buttonFilter itself.
                            if (IsotopeWrap.data('isotope')) {
                                IsotopeWrap.isotope();
                            }
                        };
                        $(document).ready(applyHashFilter);
                        $(window).on("hashchange", applyHashFilter);
                    }
                    if (IsoCount) {
                        isoFilterCounter(container, IsotopeWrap);
                    }
                    IsoDropdownFilter.on('change', function (e) {
                        e.preventDefault();
                        buttonFilter = $(this).val();
                        IsotopeWrap.isotope();
                    });
                }
            }
            container.trigger("tpg_loaded");
        });
    };
    initTpg();

    $(window).on('load resize', function () {
        tpgBottomScriptLoader();
        overlayIconResizeTpg();
    });


    function tpgBottomScriptLoader() {
        setTimeout(function () {

            $(".bottom-script-loader").fadeOut(500, function () {
                // fadeOut complete. Remove the loading div
                $(".bottom-script-loader").remove(); //makes page more lightweight
            });

        }, 400)
    }

    function windowHashChange(IsoButton) {
        var $buttons = IsoButton.find('button');
        var $hashFilter = decodeHash();

        // Only accept a filter that one of this grid's own buttons provides.
        if (!isValidIsoFilter($hashFilter) || !$buttons.filter(function () {
            return $(this).attr('data-filter') === $hashFilter;
        }).length) {
            $hashFilter = $buttons.filter('.selected').attr('data-filter') || '';
            $hashFilter = isValidIsoFilter($hashFilter) ? $hashFilter : '*';
        }
        // The caller stores the returned filter; the grid's own filter function
        // then matches items by class, so no string reaches isotope or jQuery.
        $buttons.removeClass("selected");
        $buttons.filter(function () {
            return $(this).attr('data-filter') === $hashFilter;
        }).addClass("selected");

        return $hashFilter;
    }

    function isoItemMatches($item, filter) {
        if (filter === '*') {
            return true;
        }
        if (!isValidIsoFilter(filter)) {
            return false;
        }
        return $item.hasClass(filter.slice(1));
    }

    function escapeRegExp(value) {
        return String(value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function decodeHash() {
        var $matches = location.hash.match(/filter=([^&]+)/i);
        var $hashFilter = $matches && $matches[1];
        if (!$hashFilter) {
            return '';
        }
        try {
            return decodeURIComponent($hashFilter);
        } catch (e) {
            return '';
        }
    }

    function isIsoUrlGrid($isotope) {
        // The "filter in URL" option is only trusted from the server-printed
        // list, never from a data-url attribute that post content can carry.
        var id = $isotope.attr('id') || '';
        var allowed = window.rttpgIsoUrlGrids;
        return !!id && Array.isArray(allowed) && allowed.indexOf(id) !== -1 && document.getElementById(id) === $isotope[0];
    }

    function isValidIsoFilter(filter) {
        return typeof filter === 'string' && /^(\*|\.[\w-]+)$/.test(filter);
    }

    function isoFilterCounter(container, isotope) {
        var total = 0;
        container.find('.rt-tpg-isotope-buttons button').each(function () {
            var self = $(this),
                filter = self.attr("data-filter"),
                itemTotal = isValidIsoFilter(filter) ? isotope.find(filter).length : 0;
            if (filter != "*") {
                self.find('span').remove();
                self.append("<span> (" + itemTotal + ") </span>");
                total = total + itemTotal;
            }
        });
        container.find('.rt-tpg-isotope-buttons button[data-filter="*"]').find('span').remove();
        container.find('.rt-tpg-isotope-buttons button[data-filter="*"]').append("<span> (" + total + ") </span>");
    }

    // debounce so filtering doesn't happen every millisecond
    function debounce(fn, threshold) {
        var timeout;
        return function debounced() {
            if (timeout) {
                clearTimeout(timeout);
            }

            function delayed() {
                fn();
                timeout = null;
            }

            setTimeout(delayed, threshold || 100);
        };
    }

    function preFunction() {
        //HeightResize();
        overlayIconResizeTpg();
    }

    $(".rt-tpg-container a.disabled").each(function () {
        $(this).prop("disabled", true);
        $(this).removeAttr("href");
    });

    function HeightResize() {
        var wWidth = $(window).width();
        tpgFixLazyLoad();
        $(".rt-tpg-container").each(function () {
            var self = $(this),
                dCol = self.data('desktop-col'),
                tCol = self.data('tab-col'),
                mCol = self.data('mobile-col'),
                target = $(this).find('.rt-row.rt-content-loader.tpg-even');
            if ((wWidth >= 992 && dCol > 1) || (wWidth >= 768 && tCol > 1) || (wWidth < 768 && mCol > 1)) {
                target.imagesLoaded(function () {
                    var tlpMaxH = 0;
                    target.find('.even-grid-item').height('auto');
                    target.find('.even-grid-item').each(function () {
                        var $thisH = $(this).outerHeight();
                        if ($thisH > tlpMaxH) {
                            tlpMaxH = $thisH;
                        }
                    });
                    target.find('.even-grid-item').height(tlpMaxH + "px");
                });
            } else {
                target.find('.even-grid-item').height('auto');
            }

        });
        if ($(".rt-row.rt-content-loader.layout4").length) {
            equalHeight4Layout4();
        }

        function equalHeight4Layout4() {
            var $maxH = $(".rt-row.rt-content-loader.layout4 .layout4item").height();
            $(".rt-row.rt-content-loader.layout4 .layout4item .layoutInner .rt-img-holder img,.rt-row.rt-content-loader.layout4 .layout4item .layoutInner.layoutInner-content").height($maxH + "px");
        }
    }

    function overlayIconResizeTpg() {
        $('.overlay').each(function () {
            var holder_height = jQuery(this).height();
            var target = $(this).children('.link-holder');
            var targetd = $(this).children('.view-details');
            var a_height = target.height();
            var ad_height = targetd.height();
            var h = (holder_height - a_height) / 2;
            var hd = (holder_height - ad_height) / 2;
            target.css('top', h + 'px');
            targetd.css('margin-top', hd + 'px');
        });
    }


    function RTPromoContent() {
        parent.document.addEventListener("mousedown", function (e) {
            var widgets = parent.document.querySelectorAll(".elementor-element--promotion");

            if (widgets.length > 0) {
                for (var i = 0; i < widgets.length; i++) {
                    if (widgets[i].contains(e.target)) {
                        var dialog = parent.document.querySelector("#elementor-element--promotion__dialog");
                        var icon = widgets[i].querySelector(".icon > i");

                        if (icon.classList.toString().indexOf("tss-promotional-element") >= 0) {
                            dialog.querySelector(".dialog-buttons-action").style.display = "none";

                            if (dialog.querySelector(".rt-dialog-buttons-action") === null) {
                                var button = document.createElement("a");
                                var buttonText = document.createTextNode("Get Pro");

                                button.setAttribute("href", "//www.radiustheme.com/downloads/the-post-grid-pro-for-wordpress/");
                                button.setAttribute("target", "_blank");
                                button.classList.add(
                                    "elementor-button",
                                    "elementor-button-success",
                                    "rt-dialog-buttons-action"
                                );

                                button.appendChild(buttonText);

                                // dialog.querySelector(".dialog-buttons-action").removeChild(button.outerHTML);
                                dialog.querySelector(".dialog-buttons-action").insertAdjacentHTML("afterend", button.outerHTML);
                            } else {
                                dialog.querySelector(".rt-dialog-buttons-action").style.display = "";
                            }
                        } else {
                            dialog.querySelector(".dialog-buttons-action").style.display = "";

                            if (dialog.querySelector(".rt-dialog-buttons-action") !== null) {
                                dialog.querySelector(".rt-dialog-buttons-action").style.display = "none";
                            }
                        }

                        break;
                    }
                }
            }
        });
    }

    // Elementor Frontend Load
    $(window).on('elementor/frontend/init', function () {
        if (elementorFrontend.isEditMode()) {
            // Promo Content.
            RTPromoContent();
        }
    });

})(jQuery);