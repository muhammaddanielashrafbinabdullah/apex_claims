import Alpine from 'alpinejs';
import claimCalculator from './claim-calculator';
import adminReview from './admin-review';
import claimView from './claim-view';

window.Alpine = Alpine;
Alpine.data('claimCalculator', claimCalculator);
Alpine.data('adminReview', adminReview);
Alpine.data('claimView', claimView);
Alpine.start();
