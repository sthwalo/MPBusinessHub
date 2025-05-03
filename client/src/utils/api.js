import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  transformResponse: [function (data) {
    try {
      return JSON.parse(data);
    } catch (e) {
      return data;
    }
  }]
});

// Request interceptor for auth token
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('mpbh_token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => Promise.reject(error)
);

// Enhanced response interceptor
api.interceptors.response.use(
  response => {
    if (typeof response.data === 'string' && 
        (response.data.startsWith('<!DOCTYPE') || 
         response.data.startsWith('<br />') || 
         response.data.startsWith('<html'))) {
      throw {
        response: {
          status: 500,
          data: {
            message: 'Server returned HTML instead of JSON',
            htmlError: response.data
          }
        }
      };
    }
    return response.data;
  },
  error => {
    if (!error.response) {
      return Promise.reject({
        status: 0,
        message: 'Network Error - Unable to connect to server',
        originalError: error
      });
    }

    const { status, data } = error.response;
    
    if (status === 401) {
      localStorage.removeItem('mpbh_token');
      localStorage.removeItem('mpbh_user');
    }
    
    if (typeof data === 'string' && (data.startsWith('<') || data.includes('</html>'))) {
      return Promise.reject({
        status,
        message: 'Server returned an HTML error page',
        htmlError: data,
        originalError: error
      });
    }
    
    return Promise.reject({
      status,
      message: data?.message || error.message || `Request failed with status ${status}`,
      errors: data?.errors,
      originalError: error
    });
  }
);

// API functions
export const fetchPackageTiers = async () => {
  try {
    return await api.get('/packages/tiers');
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

// Debugging in development
if (process.env.NODE_ENV === 'development') {
  api.interceptors.request.use(request => {
    console.log('Starting Request', request);
    return request;
  });

  api.interceptors.response.use(response => {
    console.log('Response:', response);
    return response;
  });
}

export default api;