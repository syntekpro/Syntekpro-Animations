(function($) {
    'use strict';

    $(document).ready(function() {
        var exportBtn = $('#presets-export-json');
        var importBtn = $('#presets-import-json');
        var importFile = $('#presets-import-file');
        var presetsData = (window.syntekproAdminPresets && window.syntekproAdminPresets.presets) || {};

        if (exportBtn.length) {
            exportBtn.on('click', function(e) {
                e.preventDefault();
                var payload = buildExportPayload(presetsData);
                downloadJSON(payload, 'syntekplus-presets.json');
                toast('Exported ' + payload.presets.length + ' presets to JSON.', 'success');
            });
        }

        if (importBtn.length && importFile.length) {
            importBtn.on('click', function(e) {
                e.preventDefault();
                importFile.trigger('click');
            });

            importFile.on('change', function(e) {
                var file = e.target.files && e.target.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function(ev) {
                    try {
                        var data = JSON.parse(ev.target.result || '{}');
                        if (!validateImport(data)) {
                            toast('Invalid presets file. Expecting { version, presets: [] }', 'error');
                            importFile.val('');
                            return;
                        }
                        // Persist last import in localStorage for convenience
                        try {
                            localStorage.setItem('syntekpro_imported_presets', JSON.stringify(data));
                        } catch (storageErr) {}
                        toast('Imported ' + data.presets.length + ' presets. Downloaded copy ready.', 'success');
                        downloadJSON(data, 'syntekplus-presets-imported.json');
                    } catch (err) {
                        toast('Could not parse JSON file.', 'error');
                    }
                    importFile.val('');
                };
                reader.readAsText(file);
            });
        }
    });

    function buildExportPayload(presetsObj) {
        var list = [];
        Object.keys(presetsObj || {}).forEach(function(key) {
            var preset = presetsObj[key] || {};
            list.push({
                name: preset.name || key,
                type: key,
                category: preset.category || 'general',
                engine: 'auto',
                trigger: 'scroll',
                duration: 1,
                delay: 0,
                from: preset.from || {},
                to: preset.to || {}
            });
        });
        return {
            version: '2.0.0',
            presets: list
        };
    }

    function validateImport(data) {
        if (!data || !Array.isArray(data.presets)) return false;
        return data.presets.every(function(item) {
            return item && typeof item.type === 'string' && typeof item.name === 'string';
        });
    }

    function downloadJSON(obj, filename) {
        var blob = new Blob([JSON.stringify(obj, null, 2)], { type: 'application/json' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function toast(message, variant) {
        var cls = variant === 'error' ? 'notice-error' : 'notice-success';
        var $notice = $('<div class="notice ' + cls + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap').first().prepend($notice);
        setTimeout(function() {
            $notice.fadeOut(200, function() { $(this).remove(); });
        }, 4000);
    }
})(jQuery);
