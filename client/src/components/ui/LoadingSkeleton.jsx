import { memo } from 'react';

/**
 * LoadingSkeleton component for displaying a loading state
 * @returns {JSX.Element}
 */
const LoadingSkeleton = () => (
  <div className="container mx-auto px-4 py-8">
    <div className="bg-white rounded-lg shadow-md p-6 text-center">
      <div className="animate-pulse">
        <div className="h-8 bg-gray-200 rounded w-1/2 mx-auto mb-4"></div>
        <div className="h-4 bg-gray-200 rounded w-1/3 mx-auto mb-6"></div>
        <div className="h-64 bg-gray-200 rounded mb-6"></div>
        <div className="h-4 bg-gray-200 rounded mb-2"></div>
        <div className="h-4 bg-gray-200 rounded mb-2"></div>
        <div className="h-4 bg-gray-200 rounded"></div>
      </div>
      <p className="mt-4 text-gray-600">Loading business details...</p>
    </div>
  </div>
);

export default memo(LoadingSkeleton);