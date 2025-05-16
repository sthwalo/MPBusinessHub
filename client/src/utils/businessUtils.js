/**
 * Utility functions for business-related components
 */

/**
 * Calculate the average rating from an array of reviews
 * @param {Array} reviews - Array of review objects with rating property
 * @param {Number} newRating - Optional new rating to include in calculation
 * @returns {Number} - The calculated average rating
 */
export const calculateAverageRating = (reviews, newRating = 0) => {
  if (!Array.isArray(reviews) || reviews.length === 0) {
    return newRating || 0;
  }
  const total = reviews.reduce((sum, r) => sum + (Number(r.rating) || 0), 0) + (Number(newRating) || 0);
  return total / (reviews.length + (newRating ? 1 : 0));
};

/**
 * Package features mapping - defines what features are available for each package type
 */
export const packageFeatures = {
  Basic: { products: false, contact: false },
  Bronze: { products: false, contact: true },
  Silver: { products: true, contact: true },
  Gold: { products: true, contact: true }
};

/**
 * SVG path definitions for various icons
 */
export const iconPaths = {
  phone: "M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z",
  email: "M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
  website: "M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9",
  address: "M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z",
  location: "M15 11a3 3 0 11-6 0 3 3 0 016 0z",
  category: "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
};