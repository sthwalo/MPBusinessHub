import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});
// Debugging in development
if (import.meta.env.MODE === 'development') {
  api.interceptors.request.use(request => {
    console.log('Starting Request', request);
    console.log('Request URL:', request.url); 
    console.log('Full URL:', request.baseURL + request.url); 
    return request;
  });
}


// Add request interceptor
api.interceptors.request.use(config => {
  const token = localStorage.getItem('mpbh_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Add response interceptor
api.interceptors.response.use(
  response => response.data,
  error => {
    if (!error.response) {
      return Promise.reject({
        status: 0,
        message: 'Network Error',
        originalError: error
      });
    }
    return Promise.reject(error.response.data);
  }
);

// API functions
export const fetchPackageTiers = async () => {
  try {
    const response = await api.get('/packages');
    const transformed = response.reduce((acc, pkg) => ({
      ...acc,
      [pkg.name]: {
        price_monthly: parseFloat(pkg.price_monthly),
        price_yearly: parseFloat(pkg.price_annual),
        features: {
          ...pkg.features,
          social_media_feature: pkg.name === 'Gold' ? 2 : pkg.name === 'Silver' ? 1 : 0,
          monthly_adverts: pkg.advert_limit,
          product_catalog: pkg.product_limit > 0
        }
      }
    }), {});
    
    return { data: transformed };
  } catch (error) {
    if (error.htmlError) {
      console.error('Server Error:', error.htmlError);
      throw new Error('Server configuration error - received HTML response');
    }
    throw error;
  }
};

// Adverts API functions
export const advertsApi = {
  // Get all adverts for the authenticated user's business
  getAll: async () => {
    try {
      return await api.get('/adverts');
    } catch (error) {
      console.error('Error fetching adverts:', error);
      throw error;
    }
  },
  
  // Create a new advert
  create: async (advertData) => {
    try {
      return await api.post('/adverts', advertData);
    } catch (error) {
      console.error('Error creating advert:', error);
      throw error;
    }
  },
  
  // Delete an advert
  delete: async (advertId) => {
    try {
      return await api.delete(`/adverts/${advertId}`);
    } catch (error) {
      console.error('Error deleting advert:', error);
      throw error;
    }
  }
};

// Products API functions
export const productsApi = {
  // Get all products for the authenticated user's business
  getAll: async () => {
    try {
      return await api.get('/products');
    } catch (error) {
      console.error('Error fetching products:', error);
      throw error;
    }
  },
  
  // Get products for a specific business
  getForBusiness: async (businessId) => {
    try {
      return await api.get(`/businesses/${businessId}/products`);
    } catch (error) {
      console.error('Error fetching business products:', error);
      throw error;
    }
  },
  
  // Create a new product
  create: async (productData) => {
    try {
      return await api.post('/products', productData);
    } catch (error) {
      console.error('Error creating product:', error);
      throw error;
    }
  },
  
  // Get a specific product
  get: async (productId) => {
    try {
      return await api.get(`/products/${productId}`);
    } catch (error) {
      console.error('Error fetching product:', error);
      throw error;
    }
  },
  
  // Update a product
  update: async (productId, productData) => {
    try {
      return await api.put(`/products/${productId}`, productData);
    } catch (error) {
      console.error('Error updating product:', error);
      throw error;
    }
  },
  
  // Delete a product
  delete: async (productId) => {
    try {
      return await api.delete(`/products/${productId}`);
    } catch (error) {
      console.error('Error deleting product:', error);
      throw error;
    }
  }
};

// Business metadata API functions
export const businessMetadataApi = {
  // Get all unique business categories
  getCategories: async () => {
    try {
      const response = await api.get('/categories');
      // The response interceptor already returns response.data
      return response;
    } catch (error) {
      console.error('Error fetching categories:', error);
      throw error;
    }
  },
  
  // Get all unique business districts
  getDistricts: async () => {
    try {
      const response = await api.get('/districts');
      // The response interceptor already returns response.data
      return response;
    } catch (error) {
      console.error('Error fetching districts:', error);
      throw error;
    }
  }
};

// Debugging in development
if (import.meta.env.MODE === 'development') {
  api.interceptors.request.use(request => {
    console.log('Starting Request', request);
    console.log('Request URL:', request.url); 
    console.log('Full URL:', request.baseURL + request.url); 
    return request;
  });

  api.interceptors.response.use(response => {
    console.log('Response:', response);
    return response;
  });
}

export default api;