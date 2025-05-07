import '@testing-library/jest-dom';

// Configure global test environment
beforeAll(() => {
  // Add any global setup
});

afterEach(() => {
  // Clean up after each test
  document.body.innerHTML = '';
});