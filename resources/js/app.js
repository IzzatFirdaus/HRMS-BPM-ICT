/**
 * resources/js/app.js
 *
 * This is the main JavaScript entry point for your application.
 * It imports and initializes core libraries and frameworks like Bootstrap,
 * jQuery, and Livewire.
 */

// Import Bootstrap (if you are using Bootstrap or a theme like AdminLTE)
// Ensure Bootstrap is installed via npm (e.g., npm install bootstrap)
// import 'bootstrap'; // Uncomment if using standard Bootstrap

// Import jQuery (often required by Bootstrap and other libraries like Select2)
// Ensure jQuery is installed via npm (e.g., npm install jquery)
try {
  window.$ = window.jQuery = require('jquery');
} catch (e) {
  console.error('jQuery not found or failed to load:', e);
}


// Import and initialize Livewire
// Livewire is typically auto-initialized, but explicitly importing is good practice
import './bootstrap'; // Includes Axios and other basic bootstrap
import Alpine from 'alpinejs'; // Keep if using Alpine.js (often with Livewire)

window.Alpine = Alpine;

Alpine.start();

// Livewire initialization (usually happens automatically, but can be explicit)
// import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
// Livewire.start();


// --- Frontend Libraries used by your application ---

// Example: Initialize Select2 if your application uses it (e.g., for dropdowns in forms)
// Based on Livewire dispatches like 'setSelect2Values', it's likely you are using Select2.
// Ensure Select2 is installed via npm (e.g., npm install select2)
// You might also need to import Select2 CSS in your main layout file.
try {
  require('select2'); // Import Select2 library

  // Optional: Initialize Select2 globally or on specific elements
  // You might need more specific initialization logic based on your usage
  $(document).ready(function() {
      // Example: Initialize all elements with class 'select2'
      // $('.select2').select2();

      // Listen for Livewire events to re-initialize Select2 on dynamic content
      Livewire.hook('element.added', (el) => {
          // Check if added element or its children contain Select2 elements
          // You might need more specific selectors than just '.select2'
          $(el).find('.select2').select2();
      });

      Livewire.on('setSelect2Values', (employeeId, leaveId) => {
          // Example: Set values for Select2 dropdowns based on dispatched event
          // Adjust selectors based on your actual HTML structure
          $('#employee-select').val(employeeId).trigger('change');
          $('#leave-type-select').val(leaveId).trigger('change');
      });

       Livewire.on('clearSelect2Values', () => {
           // Example: Clear Select2 values
           $('#employee-select').val(null).trigger('change');
           $('#leave-type-select').val(null).trigger('change');
       });
  });

} catch (e) {
  console.warn('Select2 not found or failed to initialize. If you are not using Select2, you can ignore this warning.', e);
}


// Add imports and initialization for any other frontend libraries here.
// Examples:
// import 'moment'; // For date/time manipulation
// import 'chart.js'; // For charts
// import 'datatables.net'; // For data tables


// --- Custom Application JavaScript ---

// Import any custom JavaScript modules or components you create
// import './my-custom-module';

// Add any global JavaScript logic here
console.log('App.js loaded.');
