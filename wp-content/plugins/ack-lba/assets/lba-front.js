/**
 * Ack LBA — Frontend assessment script
 *
 * Config is injected by PHP via wp_localize_script as `ackLbaConfig`:
 * {
 *   sections:   [ { label, color, items: [], tiers: { low_ceil, mid_ceil, low_text, mid_text, high_text } } ],
 *   thresholds: { high, low },
 *   patterns:   [ { high_idx, low_idx, title, desc } ],
 *   catch_alls: { all_low: { avg_ceil, title, desc }, balanced: { avg_floor, title, desc }, mixed: { title, desc } },
 *   general:    { title, intro, cta_heading, cta_body, cta_label, cta_email }
 * }
 */
(function () {
    'use strict';

    var cfg     = window.ackLbaConfig;
    var answers = {};
    var totalQ  = 0;

    /* ----------------------------------------------------------------
       Bootstrap
    ---------------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        if (!cfg) return;

        // Count total questions across all sections
        cfg.sections.forEach(function (sec) {
            totalQ += sec.items.length;
        });

        renderHeader();
        buildForm();
        updateProgress();
    });

    /* ----------------------------------------------------------------
       Render page header (title + intro) from config
    ---------------------------------------------------------------- */
    function renderHeader() {
        var titleEl = document.getElementById('lba-title');
        var introEl = document.getElementById('lba-intro');
        if (titleEl) titleEl.innerHTML = cfg.general.title;
        if (introEl) introEl.innerHTML = cfg.general.intro;

        var ctaH = document.getElementById('lba-cta-heading');
        var ctaB = document.getElementById('lba-cta-body');
        var ctaL = document.getElementById('lba-cta-btn');
        if (ctaH) ctaH.textContent = cfg.general.cta_heading;
        if (ctaB) ctaB.textContent = cfg.general.cta_body;
        if (ctaL) {
            ctaL.textContent = cfg.general.cta_label;
            ctaL.href = 'mailto:' + cfg.general.cta_email;
        }
    }

    /* ----------------------------------------------------------------
       Build form
    ---------------------------------------------------------------- */
    function buildForm() {
        var container = document.getElementById('lba-questions-container');
        if (!container) return;

        var globalNum = 0;

        cfg.sections.forEach(function (sec, si) {
            var section = document.createElement('div');
            section.className = 'lba-dimension';
            section.style.animationDelay = (0.3 + si * 0.15) + 's';

            // Header
            var hdr = document.createElement('div');
            hdr.className = 'lba-dimension-header';

            var dot = document.createElement('div');
            dot.className = 'lba-dimension-dot';
            dot.style.background = sec.color;

            var lbl = document.createElement('span');
            lbl.className = 'lba-dimension-label';
            lbl.textContent = sec.label;

            hdr.appendChild(dot);
            hdr.appendChild(lbl);
            section.appendChild(hdr);

            // Questions
            sec.items.forEach(function (text, qi) {
                globalNum++;
                var key  = si + '_' + qi;
                var item = document.createElement('div');
                item.className = 'lba-item';
                item.id = 'lba-item-' + key;

                var numEl = document.createElement('div');
                numEl.className = 'lba-item-num';
                numEl.textContent = globalNum + ' of ' + totalQ;

                var textEl = document.createElement('div');
                textEl.className = 'lba-item-text';
                textEl.textContent = text;

                var scale = document.createElement('div');
                scale.className = 'lba-scale';

                for (var v = 1; v <= 5; v++) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'lba-scale-btn';
                    btn.textContent = v;
                    btn.dataset.key = key;
                    btn.dataset.val = v;
                    btn.addEventListener('click', onScaleClick);
                    scale.appendChild(btn);
                }

                var scaleLabels = document.createElement('div');
                scaleLabels.className = 'lba-scale-labels';
                scaleLabels.innerHTML = '<span>Never</span><span>Always</span>';

                item.appendChild(numEl);
                item.appendChild(textEl);
                item.appendChild(scale);
                item.appendChild(scaleLabels);
                section.appendChild(item);
            });

            container.appendChild(section);
        });
    }

    /* ----------------------------------------------------------------
       Answer selection
    ---------------------------------------------------------------- */
    function onScaleClick(e) {
        var btn = e.currentTarget;
        var key = btn.dataset.key;
        var val = parseInt(btn.dataset.val, 10);

        answers[key] = val;

        // Deselect siblings
        var siblings = document.querySelectorAll('[data-key="' + key + '"]');
        siblings.forEach(function (b) { b.classList.remove('lba-selected'); });
        btn.classList.add('lba-selected');

        document.getElementById('lba-item-' + key).classList.add('lba-answered');
        updateProgress();
    }

    /* ----------------------------------------------------------------
       Progress bar
    ---------------------------------------------------------------- */
    function updateProgress() {
        var count   = Object.keys(answers).length;
        var pct     = totalQ > 0 ? (count / totalQ) * 100 : 0;
        var fillEl  = document.getElementById('lba-progress-fill');
        var countEl = document.getElementById('lba-answered-count');
        var totalEl = document.getElementById('lba-total-count');
        var btn     = document.getElementById('lba-submit-btn');
        var hint    = document.getElementById('lba-submit-hint');

        if (fillEl)  fillEl.style.width = pct + '%';
        if (countEl) countEl.textContent = count;
        if (totalEl) totalEl.textContent = totalQ;

        if (btn) {
            if (count === totalQ) {
                btn.disabled = false;
                if (hint) hint.textContent = "You're good to go.";
            } else {
                btn.disabled = true;
                if (hint) hint.textContent = 'Answer all ' + totalQ + ' to continue';
            }
        }
    }

    /* ----------------------------------------------------------------
       Scoring
    ---------------------------------------------------------------- */
    function scoreFor(si) {
        var sec   = cfg.sections[si];
        var total = 0;
        sec.items.forEach(function (_, qi) {
            total += answers[si + '_' + qi] || 0;
        });
        return parseFloat((total / sec.items.length).toFixed(1));
    }

    function getDiagnosis(si, score) {
        var tiers = cfg.sections[si].tiers;
        var text;
        if (score <= parseFloat(tiers.low_ceil)) {
            text = tiers.low_text;
        } else if (score <= parseFloat(tiers.mid_ceil)) {
            text = tiers.mid_text;
        } else {
            text = tiers.high_text;
        }
        return text;
    }

    function getTierLabel(si, score) {
        var tiers = cfg.sections[si].tiers;
        if (score <= parseFloat(tiers.low_ceil))  return 'Significant imbalance';
        if (score <= parseFloat(tiers.mid_ceil))  return 'Moderate stability';
        return 'Strong balance';
    }

    /* ----------------------------------------------------------------
       Pattern matching (fixed matrix)
    ---------------------------------------------------------------- */
    function getPatterns(scores) {
        var HIGH     = parseFloat(cfg.thresholds.high);
        var LOW      = parseFloat(cfg.thresholds.low);
        var patterns = [];

        cfg.patterns.forEach(function (rule) {
            var hi = scores[rule.high_idx];
            var lo = scores[rule.low_idx];
            if (hi !== undefined && lo !== undefined && hi >= HIGH && lo < LOW) {
                patterns.push({ title: rule.title, desc: rule.desc });
            }
        });

        if (patterns.length > 0) return patterns;

        // Catch-alls
        var sum = scores.reduce(function (acc, s) { return acc + s; }, 0);
        var avg = sum / scores.length;
        var ca  = cfg.catch_alls;

        if (avg < parseFloat(ca.all_low.avg_ceil)) {
            return [{ title: ca.all_low.title, desc: ca.all_low.desc }];
        }
        if (avg >= parseFloat(ca.balanced.avg_floor)) {
            return [{ title: ca.balanced.title, desc: ca.balanced.desc }];
        }
        return [{ title: ca.mixed.title, desc: ca.mixed.desc }];
    }

    /* ----------------------------------------------------------------
       Show results
    ---------------------------------------------------------------- */
    window.lbaShowResults = function () {
        var scores = cfg.sections.map(function (_, si) { return scoreFor(si); });
        var patterns = getPatterns(scores);

        // --- Score bars ---
        var scoreHTML = cfg.sections.map(function (sec, si) {
            var score    = scores[si];
            var pct      = ((score - 1) / 4) * 100; // scale 1-5 → 0-100%
            var tierLbl  = getTierLabel(si, score);
            var diagnosis = getDiagnosis(si, score);
            var dotColor  = sec.color;

            return '<div class="lba-score-block">'
                + '<div class="lba-score-row">'
                + '<div class="lba-score-name">'
                + '<span class="lba-score-dot" style="background:' + dotColor + '"></span>'
                + sec.label
                + '</div>'
                + '<div class="lba-score-track"><div class="lba-score-fill" style="width:0%" data-pct="' + pct + '" data-color="' + dotColor + '"></div></div>'
                + '<div class="lba-score-val">' + score + '</div>'
                + '</div>'
                + '<div class="lba-score-detail">'
                + '<div class="lba-tier-label">' + tierLbl + '</div>'
                + '<div class="lba-diagnosis">' + diagnosis + '</div>'
                + '</div>'
                + '</div>';
        }).join('');

        var scoresDisplay = document.getElementById('lba-scores-display');
        if (scoresDisplay) scoresDisplay.innerHTML = scoreHTML;

        // --- Pattern cards ---
        var patternHTML = '<h3 class="lba-patterns-heading">What this pattern suggests</h3>'
            + patterns.map(function (p) {
                return '<div class="lba-pattern-card"><h4>' + esc(p.title) + '</h4><p>' + esc(p.desc) + '</p></div>';
            }).join('');

        var patternsDisplay = document.getElementById('lba-patterns-display');
        if (patternsDisplay) patternsDisplay.innerHTML = patternHTML;

        // --- Mailto ---
        var subject  = 'LBA Results \u2014 Let\u2019s Talk';
        var bodyLines = ['Hi,', '', 'I just completed the Leadership Balance Assessment. Here are my results:', ''];
        cfg.sections.forEach(function (sec, si) {
            bodyLines.push(sec.label + ': ' + scores[si] + '/5');
        });
        bodyLines.push('');
        bodyLines.push('Pattern: ' + patterns.map(function (p) { return p.title; }).join(', '));
        bodyLines.push('');
        bodyLines.push("I\u2019d love to talk through what this means.");

        var mailtoLink = 'mailto:' + cfg.general.cta_email
            + '?subject=' + encodeURIComponent(subject)
            + '&body=' + encodeURIComponent(bodyLines.join('\n'));

        var ctaBtn = document.getElementById('lba-cta-btn');
        if (ctaBtn) ctaBtn.href = mailtoLink;

        // --- Switch views ---
        var formView    = document.getElementById('lba-form-view');
        var resultsView = document.getElementById('lba-results-view');
        if (formView)    formView.style.display = 'none';
        if (resultsView) resultsView.classList.add('lba-visible');
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Animate score bars after paint
        setTimeout(function () {
            document.querySelectorAll('.lba-score-fill').forEach(function (bar) {
                bar.style.background = bar.dataset.color;
                bar.style.width = bar.dataset.pct + '%';
            });
        }, 300);
    };

    /* ----------------------------------------------------------------
       Helpers
    ---------------------------------------------------------------- */
    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

}());
