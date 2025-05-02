import { memo } from 'react';
import { Link } from 'react-router-dom';

/**
 * ErrorMessage component for displaying error states
 * @param {Object} props
 * @param {string} props.message - Error message to display
 * @param {string} props.returnPath - Path to navigate back to
 * @returns {JSX.Element}
 */
const ErrorMessage = ({ message, returnPath = "/business-directory" }) => (
  <div className="container mx-auto px-4 py-8">
    <div className="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
      <svg className="h-12 w-12 text-red-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <h2 className="text-xl font-bold text-red-700 mb-2">Error Loading Business</h2>
      <p className="text-red-600 mb-4">{message}</p>
      <Link to={returnPath} className="btn btn-primary">
        Return to Directory
      </Link>
    </div>
  </div>
);

export default memo(ErrorMessage);