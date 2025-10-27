/**
 * OUTSINC Platform - Welcome Tour System
 * Provides an interactive guided tour for new users
 */

class WelcomeTour {
    constructor(steps, options = {}) {
        this.steps = steps;
        this.currentStep = 0;
        this.options = {
            showSkipButton: true,
            storageKey: 'tour_completed',
            onComplete: null,
            onSkip: null,
            ...options
        };
        
        this.overlay = null;
        this.tooltip = null;
        this.skipButton = null;
    }

    /**
     * Initialize and start the tour
     */
    start() {
        // Check if tour was already completed
        if (this.isCompleted() && !this.options.forceStart) {
            return;
        }

        this.createOverlay();
        this.createSkipButton();
        this.showStep(0);
    }

    /**
     * Create dark overlay
     */
    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'tour-overlay';
        this.overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            pointer-events: none;
        `;
        document.body.appendChild(this.overlay);
    }

    /**
     * Create skip button
     */
    createSkipButton() {
        if (!this.options.showSkipButton) return;

        this.skipButton = document.createElement('button');
        this.skipButton.className = 'tour-skip-btn';
        this.skipButton.textContent = 'Skip Tour';
        this.skipButton.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: white;
            border: 2px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            z-index: 10000;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        `;
        
        this.skipButton.addEventListener('click', () => this.skip());
        this.skipButton.addEventListener('mouseenter', (e) => {
            e.target.style.background = '#f0f0f0';
            e.target.style.transform = 'scale(1.05)';
        });
        this.skipButton.addEventListener('mouseleave', (e) => {
            e.target.style.background = 'white';
            e.target.style.transform = 'scale(1)';
        });
        
        document.body.appendChild(this.skipButton);
    }

    /**
     * Show a specific step
     */
    showStep(index) {
        if (index >= this.steps.length) {
            this.complete();
            return;
        }

        this.currentStep = index;
        const step = this.steps[index];

        // Remove previous tooltip
        if (this.tooltip) {
            this.tooltip.remove();
        }

        // Find target element
        const target = document.querySelector(step.element);
        if (!target) {
            console.warn(`Tour target not found: ${step.element}`);
            this.showStep(index + 1);
            return;
        }

        // Highlight target element
        this.highlightElement(target);

        // Create tooltip
        this.createTooltip(step, target);

        // Save progress
        this.saveProgress();
    }

    /**
     * Highlight target element
     */
    highlightElement(element) {
        // Remove previous highlights
        document.querySelectorAll('.tour-highlight').forEach(el => {
            el.classList.remove('tour-highlight');
        });

        // Add highlight to target
        element.classList.add('tour-highlight');
        element.style.position = 'relative';
        element.style.zIndex = '9999';

        // Scroll element into view
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    /**
     * Create and position tooltip
     */
    createTooltip(step, target) {
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'tour-tooltip';
        this.tooltip.style.cssText = `
            position: absolute;
            background: #FFF9C4;
            border: 2px solid #F9A825;
            border-radius: 8px;
            padding: 20px;
            max-width: 300px;
            z-index: 10000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            font-family: 'Comic Sans MS', cursive, sans-serif;
        `;

        // Tooltip content
        const content = `
            <div class="tour-tooltip-header" style="margin-bottom: 10px;">
                <strong style="color: #F57C00; font-size: 16px;">${step.title}</strong>
            </div>
            <div class="tour-tooltip-body" style="color: #333; margin-bottom: 15px; line-height: 1.5;">
                ${step.content}
            </div>
            <div class="tour-tooltip-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 12px; color: #666;">Step ${this.currentStep + 1} of ${this.steps.length}</span>
                <div>
                    ${this.currentStep > 0 ? '<button class="tour-btn-prev" style="margin-right: 5px;">← Back</button>' : ''}
                    <button class="tour-btn-next">${this.currentStep === this.steps.length - 1 ? 'Finish' : 'Next →'}</button>
                </div>
            </div>
        `;

        this.tooltip.innerHTML = content;

        // Position tooltip
        document.body.appendChild(this.tooltip);
        this.positionTooltip(this.tooltip, target, step.position || 'bottom');

        // Add event listeners
        const nextBtn = this.tooltip.querySelector('.tour-btn-next');
        const prevBtn = this.tooltip.querySelector('.tour-btn-prev');

        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.next());
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.prev());
        }

        // Style buttons
        this.styleButtons();
    }

    /**
     * Style tooltip buttons
     */
    styleButtons() {
        const buttons = this.tooltip.querySelectorAll('button');
        buttons.forEach(button => {
            button.style.cssText = `
                padding: 8px 16px;
                background: #F57C00;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: 600;
                transition: background 0.3s;
            `;
            
            button.addEventListener('mouseenter', (e) => {
                e.target.style.background = '#E65100';
            });
            button.addEventListener('mouseleave', (e) => {
                e.target.style.background = '#F57C00';
            });
        });
    }

    /**
     * Position tooltip relative to target
     */
    positionTooltip(tooltip, target, position) {
        const targetRect = target.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        let top, left;

        switch (position) {
            case 'top':
                top = targetRect.top - tooltipRect.height - 10;
                left = targetRect.left + (targetRect.width / 2) - (tooltipRect.width / 2);
                break;
            case 'bottom':
                top = targetRect.bottom + 10;
                left = targetRect.left + (targetRect.width / 2) - (tooltipRect.width / 2);
                break;
            case 'left':
                top = targetRect.top + (targetRect.height / 2) - (tooltipRect.height / 2);
                left = targetRect.left - tooltipRect.width - 10;
                break;
            case 'right':
                top = targetRect.top + (targetRect.height / 2) - (tooltipRect.height / 2);
                left = targetRect.right + 10;
                break;
            default:
                top = targetRect.bottom + 10;
                left = targetRect.left + (targetRect.width / 2) - (tooltipRect.width / 2);
        }

        // Ensure tooltip stays within viewport
        const maxLeft = window.innerWidth - tooltipRect.width - 20;
        const maxTop = window.innerHeight - tooltipRect.height - 20;
        
        left = Math.max(20, Math.min(left, maxLeft));
        top = Math.max(20, Math.min(top, maxTop));

        tooltip.style.top = `${top + window.scrollY}px`;
        tooltip.style.left = `${left}px`;
    }

    /**
     * Go to next step
     */
    next() {
        this.showStep(this.currentStep + 1);
    }

    /**
     * Go to previous step
     */
    prev() {
        if (this.currentStep > 0) {
            this.showStep(this.currentStep - 1);
        }
    }

    /**
     * Skip tour
     */
    skip() {
        if (confirm('Are you sure you want to skip the tour? You can restart it later from Settings.')) {
            this.cleanup();
            
            // Mark as skipped in database
            this.markAsSkipped();
            
            if (this.options.onSkip) {
                this.options.onSkip();
            }
        }
    }

    /**
     * Complete tour
     */
    complete() {
        this.cleanup();
        
        // Mark as completed in database
        this.markAsCompleted();
        
        if (this.options.onComplete) {
            this.options.onComplete();
        }

        // Show completion message
        showAlert('🎉 Tour completed! You can always restart it from Settings.', 'success');
    }

    /**
     * Clean up tour elements
     */
    cleanup() {
        if (this.overlay) {
            this.overlay.remove();
            this.overlay = null;
        }
        if (this.tooltip) {
            this.tooltip.remove();
            this.tooltip = null;
        }
        if (this.skipButton) {
            this.skipButton.remove();
            this.skipButton = null;
        }

        // Remove highlights
        document.querySelectorAll('.tour-highlight').forEach(el => {
            el.classList.remove('tour-highlight');
            el.style.zIndex = '';
        });
    }

    /**
     * Check if tour is completed
     */
    isCompleted() {
        return localStorage.getItem(this.options.storageKey) === 'true';
    }

    /**
     * Save current progress
     */
    saveProgress() {
        fetch('/api/tour-progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                current_step: this.currentStep
            })
        }).catch(err => console.error('Error saving tour progress:', err));
    }

    /**
     * Mark tour as completed
     */
    markAsCompleted() {
        localStorage.setItem(this.options.storageKey, 'true');
        
        fetch('/api/tour-progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tour_completed: true
            })
        }).catch(err => console.error('Error marking tour as completed:', err));
    }

    /**
     * Mark tour as skipped
     */
    markAsSkipped() {
        localStorage.setItem(this.options.storageKey, 'true');
        
        fetch('/api/tour-progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tour_skipped: true
            })
        }).catch(err => console.error('Error marking tour as skipped:', err));
    }

    /**
     * Reset tour
     */
    static reset() {
        localStorage.removeItem('tour_completed');
        
        fetch('/api/tour-progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                reset: true
            })
        }).catch(err => console.error('Error resetting tour:', err));
    }
}

// Add highlight styles
const style = document.createElement('style');
style.textContent = `
    .tour-highlight {
        box-shadow: 0 0 0 4px rgba(245, 124, 0, 0.5), 0 0 20px rgba(245, 124, 0, 0.3) !important;
        transition: box-shadow 0.3s;
    }
`;
document.head.appendChild(style);
