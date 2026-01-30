// Syntekpro Animations - Builder Timeline Skeleton
(function() {
    'use strict';

    if (typeof window.syntekproTimeline !== 'undefined') return;

    var STEP_ANIMATIONS = ['fadeIn', 'slideLeft', 'scaleUp', 'rotateIn', 'zoomIn'];
    var STEP_SCALE = 90; // pixels per second

    function $(selector, scope) {
        return (scope || document).querySelector(selector);
    }

    function createTrack(builder) {
        var track = builder.querySelector('.timeline-track');
        if (track) return track;
        track = document.createElement('div');
        track.className = 'timeline-track';
        builder.insertBefore(track, $('#add-timeline-step', builder));
        return track;
    }

    function createBlock(stepIndex, animation, duration, delay) {
        var block = document.createElement('div');
        block.className = 'timeline-block';
        block.innerHTML = '<strong>Step ' + stepIndex + '</strong><span>' + animation + ' • ' + duration + 's</span>';

        var startPx = (delay) * STEP_SCALE;
        var widthPx = Math.max(0.25, duration) * STEP_SCALE;
        block.style.left = startPx + 'px';
        block.style.width = widthPx + 'px';
        return block;
    }

    function readStepData(stepEl) {
        var animation = stepEl.querySelector('.step-animation')?.value || 'fadeIn';
        var duration = parseFloat(stepEl.querySelector('.step-duration')?.value || '1');
        var delay = parseFloat(stepEl.querySelector('.step-delay')?.value || '0');
        return { animation: animation, duration: duration, delay: delay };
    }

    function updateTimeline(builder) {
        var stepsContainer = $('#timeline-steps', builder);
        var track = createTrack(builder);
        if (!stepsContainer || !track) return;

        track.innerHTML = '';
        var cumulative = 0;
        var steps = stepsContainer.querySelectorAll('.timeline-step');
        steps.forEach(function(stepEl, idx) {
            var data = readStepData(stepEl);
            var start = cumulative + data.delay;
            var block = createBlock(idx + 1, data.animation, data.duration, start);
            track.appendChild(block);
            cumulative = start + data.duration;
        });
    }

    function attachStepHandlers(builder) {
        var stepsContainer = $('#timeline-steps', builder);
        if (!stepsContainer) return;

        stepsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-timeline-step')) {
                e.preventDefault();
                var step = e.target.closest('.timeline-step');
                if (step) {
                    step.remove();
                    updateTimeline(builder);
                }
            }
        });

        stepsContainer.addEventListener('input', function(e) {
            if (e.target.matches('.step-duration, .step-delay, .step-animation')) {
                updateTimeline(builder);
            }
        });
    }

    function addStep(builder, data) {
        var stepsContainer = $('#timeline-steps', builder);
        if (!stepsContainer) return;
        var newIndex = stepsContainer.querySelectorAll('.timeline-step').length + 1;
        var anim = (data && data.animation) || 'fadeIn';
        var duration = (data && data.duration) || 1;
        var delay = (data && data.delay) || 0;

        var step = document.createElement('div');
        step.className = 'timeline-step';
        step.innerHTML = '' +
            '<div class="timeline-handle">⋮⋮</div>' +
            '<div class="step-content">' +
                '<h4>Step ' + newIndex + '</h4>' +
                '<label>Animation:' +
                    '<select class="step-animation">' +
                        STEP_ANIMATIONS.map(function(animKey) { return '<option value="' + animKey + '"' + (animKey === anim ? ' selected' : '') + '>' + animKey + '</option>'; }).join('') +
                    '</select>' +
                '</label>' +
                '<label>Duration:' +
                    '<input type="number" class="step-duration" value="' + duration + '" step="0.1" min="0">' +
                '</label>' +
                '<label>Delay:' +
                    '<input type="number" class="step-delay" value="' + delay + '" step="0.1" min="0">' +
                '</label>' +
                '<button class="remove-timeline-step">Remove</button>' +
            '</div>';

        stepsContainer.appendChild(step);
        updateTimeline(builder);
    }

    function bindControls(builder) {
        var addBtn = $('#add-timeline-step', builder);
        var playBtn = $('#play-timeline', builder);
        var preview = $('#timeline-preview-box', builder);

        if (addBtn) {
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addStep(builder);
            });
        }

        if (playBtn && preview) {
            playBtn.addEventListener('click', function(e) {
                e.preventDefault();
                preview.classList.remove('is-playing');
                // brief visual feedback
                void preview.offsetWidth;
                preview.classList.add('is-playing');
                setTimeout(function() { preview.classList.remove('is-playing'); }, 900);
            });
        }

        var exportBtn = $('#timeline-export', builder);
        var importBtn = $('#timeline-import', builder);
        var importFile = $('#timeline-import-file', builder);
        var saveLocalBtn = $('#timeline-save-local', builder);
        var loadLocalBtn = $('#timeline-load-local', builder);

        if (exportBtn) {
            exportBtn.addEventListener('click', function(e) {
                e.preventDefault();
                downloadJSON(serializeSteps(builder), 'syntekpro-timeline.json');
            });
        }

        if (importBtn && importFile) {
            importBtn.addEventListener('click', function(e) {
                e.preventDefault();
                importFile.click();
            });
            importFile.addEventListener('change', function(e) {
                var file = e.target.files && e.target.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function(ev) {
                    try {
                        var data = JSON.parse(ev.target.result || '{}');
                        if (validateTimeline(data)) {
                            populateFromData(builder, data);
                            updateTimeline(builder);
                        } else {
                            alert('Invalid timeline JSON.');
                        }
                    } catch (err) {
                        alert('Could not parse JSON file.');
                    }
                };
                reader.readAsText(file);
                importFile.value = '';
            });
        }

        if (saveLocalBtn) {
            saveLocalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                try {
                    localStorage.setItem('syntekpro_timeline', JSON.stringify(serializeSteps(builder)));
                    alert('Timeline saved to this browser.');
                } catch (err) {
                    alert('Unable to save timeline locally.');
                }
            });
        }

        if (loadLocalBtn) {
            loadLocalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                try {
                    var raw = localStorage.getItem('syntekpro_timeline');
                    if (!raw) {
                        alert('No saved timeline found.');
                        return;
                    }
                    var data = JSON.parse(raw);
                    if (validateTimeline(data)) {
                        populateFromData(builder, data);
                        updateTimeline(builder);
                    } else {
                        alert('Saved timeline is invalid.');
                    }
                } catch (err) {
                    alert('Unable to load saved timeline.');
                }
            });
        }
    }

    function enableSortable(builder) {
        if (window.jQuery && typeof jQuery.fn.sortable === 'function') {
            jQuery('#timeline-steps').sortable({
                handle: '.timeline-handle',
                update: function() {
                    updateTimeline(builder);
                }
            });
        }
    }

    window.syntekproTimeline = {
        init: function() {
            var builder = document.getElementById('timeline-builder');
            if (!builder) return;
            attachStepHandlers(builder);
            bindControls(builder);
            enableSortable(builder);
            updateTimeline(builder);
        }
    };

    function serializeSteps(builder) {
        var stepsContainer = $('#timeline-steps', builder);
        var steps = [];
        if (!stepsContainer) return { version: '2.0.0', steps: steps };
        stepsContainer.querySelectorAll('.timeline-step').forEach(function(stepEl) {
            var data = readStepData(stepEl);
            steps.push({
                animation: data.animation,
                duration: data.duration,
                delay: data.delay
            });
        });
        return { version: '2.0.0', steps: steps };
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

    function validateTimeline(data) {
        return data && Array.isArray(data.steps);
    }

    function populateFromData(builder, data) {
        var stepsContainer = $('#timeline-steps', builder);
        if (!stepsContainer || !validateTimeline(data)) return;
        stepsContainer.innerHTML = '';
        data.steps.forEach(function(step) {
            addStep(builder, step);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof syntekproTimeline !== 'undefined') {
            syntekproTimeline.init();
        }
    });
})();
