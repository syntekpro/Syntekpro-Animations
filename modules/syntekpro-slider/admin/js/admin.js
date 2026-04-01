/* ==========================================================================
   SyntekPro Slider — Admin List Page JavaScript
   ========================================================================== */
(function ($) {
    'use strict';

    var Admin = {

        init: function () {
            Admin.bindCreateForm();
            Admin.bindDeleteButtons();
            Admin.bindDuplicateButtons();
            Admin.bindCopyShortcode();
        },

        // ── Create Slider ────────────────────────────────────────────────────

        bindCreateForm: function () {
            var $btn  = $('#spslider-create-btn');
            var $form = $('#spslider-create-form');
            var $name = $('#spslider-new-name');
            var $sub  = $('#spslider-confirm-create');
            var $cancel = $('#spslider-cancel-create');

            $cancel.on('click', function () {
                $form.slideUp(200);
            });

            $btn.on('click', function () {
                $form.slideToggle(200);
                $name.trigger('focus');
            });

            $sub.on('click', function () {
                var name = $.trim($name.val());
                if (!name) {
                    $name.addClass('spslider-input-error');
                    return;
                }
                $name.removeClass('spslider-input-error');
                $sub.prop('disabled', true).text(SPSLIDER_ADMIN.i18n.creating);

                $.post(SPSLIDER_ADMIN.ajax_url, {
                    action:    'spslider_create_slider',
                    nonce:     SPSLIDER_ADMIN.nonce,
                    name:      name,
                }, function (res) {
                    if (res.success && res.data && res.data.editor_url) {
                        window.location.href = res.data.editor_url;
                    } else {
                        Admin.showNotice(res.data || SPSLIDER_ADMIN.i18n.error_generic, 'error');
                        $sub.prop('disabled', false).text(SPSLIDER_ADMIN.i18n.create);
                    }
                }).fail(function () {
                    Admin.showNotice(SPSLIDER_ADMIN.i18n.error_generic, 'error');
                    $sub.prop('disabled', false).text(SPSLIDER_ADMIN.i18n.create);
                });
            });

            // Enter key submits
            $name.on('keydown', function (e) {
                if (e.which === 13) $sub.trigger('click');
            });
        },

        // ── Delete Slider ─────────────────────────────────────────────────────

        bindDeleteButtons: function () {
            $(document).on('click', '.spslider-btn-delete', function (e) {
                e.preventDefault();
                var $card = $(this).closest('.spslider-card');
                var id    = $(this).data('id');
                var name  = $card.find('.spslider-card-title').text();

                if (!window.confirm(SPSLIDER_ADMIN.i18n.confirm_delete.replace('{name}', name))) return;

                $(this).prop('disabled', true);

                $.post(SPSLIDER_ADMIN.ajax_url, {
                    action:    'spslider_delete_slider',
                    nonce:     SPSLIDER_ADMIN.nonce,
                    slider_id: id,
                }, function (res) {
                    if (res.success) {
                        $card.animate({ opacity: 0, height: 0 }, 300, function () {
                            $card.remove();
                            if (!$('.spslider-card').length) {
                                location.reload();
                            }
                        });
                    } else {
                        Admin.showNotice(res.data || SPSLIDER_ADMIN.i18n.error_generic, 'error');
                    }
                }).fail(function () {
                    Admin.showNotice(SPSLIDER_ADMIN.i18n.error_generic, 'error');
                });
            });
        },

        // ── Duplicate Slider ──────────────────────────────────────────────────

        bindDuplicateButtons: function () {
            $(document).on('click', '.spslider-btn-duplicate', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var id   = $btn.data('id');

                $btn.prop('disabled', true);

                $.post(SPSLIDER_ADMIN.ajax_url, {
                    action:    'spslider_duplicate_slider',
                    nonce:     SPSLIDER_ADMIN.nonce,
                    slider_id: id,
                }, function (res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        Admin.showNotice(res.data || SPSLIDER_ADMIN.i18n.error_generic, 'error');
                        $btn.prop('disabled', false);
                    }
                }).fail(function () {
                    Admin.showNotice(SPSLIDER_ADMIN.i18n.error_generic, 'error');
                    $btn.prop('disabled', false);
                });
            });
        },

        // ── Copy Shortcode ────────────────────────────────────────────────────

        bindCopyShortcode: function () {
            $(document).on('click', '.spslider-copy-btn', function () {
                var $btn   = $(this);
                var code   = $btn.closest('.spslider-card-shortcode').find('code').text();
                var $input = $('<textarea>').val(code).appendTo(document.body).select();

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(code).then(function () {
                        Admin.flashCopied($btn);
                    }).catch(function () {
                        document.execCommand('copy');
                        Admin.flashCopied($btn);
                    });
                } else {
                    document.execCommand('copy');
                    Admin.flashCopied($btn);
                }
                $input.remove();
            });
        },

        flashCopied: function ($btn) {
            var orig = $btn.text();
            $btn.text(SPSLIDER_ADMIN.i18n.copied).addClass('copied');
            setTimeout(function () {
                $btn.text(orig).removeClass('copied');
            }, 1800);
        },

        // ── Notices ───────────────────────────────────────────────────────────

        showNotice: function (msg, type) {
            var $notice = $('<div class="notice notice-' + (type || 'success') + ' is-dismissible"><p>' + msg + '</p></div>');
            $('.wrap h1').after($notice);
            setTimeout(function () { $notice.fadeOut(400, function () { $notice.remove(); }); }, 4000);
        },
    };

    $(document).ready(function () { Admin.init(); });

})(jQuery);
