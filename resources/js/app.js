import Alpine from 'alpinejs';
import claimCalculator from './claim-calculator';

window.Alpine = Alpine;
Alpine.data('claimCalculator', claimCalculator);
Alpine.start();
