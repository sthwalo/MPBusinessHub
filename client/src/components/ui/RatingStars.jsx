import { memo } from 'react';

/**
 * RatingStars component for displaying and selecting ratings
 * @param {Object} props
 * @param {boolean} props.interactive - Whether stars can be clicked
 * @param {number} props.value - Current rating value (1-5)
 * @param {Function} props.onChange - Callback when rating changes
 * @returns {JSX.Element}
 */
const RatingStars = ({ interactive = false, value = 0, onChange }) => (
  <div className="flex items-center">
    {[1, 2, 3, 4, 5].map((star) => (
      <button
        key={star}
        type="button"
        onClick={() => interactive && onChange && onChange(star)}
        className={`${interactive ? 'cursor-pointer hover:text-yellow-400' : 'cursor-default'} focus:outline-none`}
        disabled={!interactive}
        aria-label={`${star} star${star !== 1 ? 's' : ''}`}
      >
        <svg 
          className={`h-8 w-8 ${value >= star ? 'text-yellow-400' : 'text-gray-300'}`}
          fill="currentColor" 
          viewBox="0 0 20 20"
        >
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      </button>
    ))}
  </div>
);

export default memo(RatingStars);