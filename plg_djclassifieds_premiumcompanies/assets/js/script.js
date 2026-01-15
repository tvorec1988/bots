/**
 * Premium Companies Plugin JavaScript
 * @package     Joomla.Plugin
 * @subpackage  Content.PremiumCompanies
 * @copyright   Copyright (C) 2026. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize premium companies display
        initPremiumCompanies();
    });

    /**
     * Initialize premium companies functionality
     */
    function initPremiumCompanies() {
        const companyCards = document.querySelectorAll('.premium-company-card');

        if (!companyCards.length) {
            return;
        }

        // Add click tracking (optional analytics)
        companyCards.forEach(function(card) {
            card.addEventListener('click', function(e) {
                // Track company card clicks
                const companyName = card.querySelector('.company-name a');
                if (companyName) {
                    console.log('Premium company clicked:', companyName.textContent);
                }
            });
        });

        // Add smooth scroll animation
        const container = document.querySelector('.premium-companies-container');
        if (container) {
            observeIntersection(container);
        }
    }

    /**
     * Observe intersection for animation triggers
     * @param {Element} element
     */
    function observeIntersection(element) {
        if (!('IntersectionObserver' in window)) {
            return;
        }

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        observer.observe(element);
    }

})();
