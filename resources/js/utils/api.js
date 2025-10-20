/**
 * API utility functions for consistent error handling
 */

/**
 * Make an API request with proper error handling
 * @param {string} url - The API endpoint
 * @param {Object} options - Fetch options
 * @returns {Promise<Object>} - The response data
 */
export async function apiRequest(url, options = {}) {
  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('token')}`
    }
  };

  const finalOptions = {
    ...defaultOptions,
    ...options,
    headers: {
      ...defaultOptions.headers,
      ...options.headers
    }
  };

  try {
    const response = await fetch(url, finalOptions);
    
    // Check if response is ok
    if (!response.ok) {
      const contentType = response.headers.get('content-type');
      
      if (contentType && contentType.includes('application/json')) {
        try {
          const errorData = await response.json();
          throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
        } catch (parseError) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
      } else {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
    }

    // Check if response has content
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      throw new Error('Invalid response format. Expected JSON.');
    }

    return await response.json();
  } catch (error) {
    console.error('API Request Error:', error);
    throw error;
  }
}

/**
 * Make a GET request
 * @param {string} url - The API endpoint
 * @returns {Promise<Object>} - The response data
 */
export async function apiGet(url) {
  return apiRequest(url, { method: 'GET' });
}

/**
 * Make a POST request
 * @param {string} url - The API endpoint
 * @param {Object} data - The data to send
 * @returns {Promise<Object>} - The response data
 */
export async function apiPost(url, data) {
  return apiRequest(url, {
    method: 'POST',
    body: JSON.stringify(data)
  });
}

/**
 * Make a PUT request
 * @param {string} url - The API endpoint
 * @param {Object} data - The data to send
 * @returns {Promise<Object>} - The response data
 */
export async function apiPut(url, data) {
  return apiRequest(url, {
    method: 'PUT',
    body: JSON.stringify(data)
  });
}

/**
 * Make a PATCH request
 * @param {string} url - The API endpoint
 * @param {Object} data - The data to send
 * @returns {Promise<Object>} - The response data
 */
export async function apiPatch(url, data) {
  return apiRequest(url, {
    method: 'PATCH',
    body: JSON.stringify(data)
  });
}

/**
 * Make a DELETE request
 * @param {string} url - The API endpoint
 * @returns {Promise<Object>} - The response data
 */
export async function apiDelete(url) {
  return apiRequest(url, { method: 'DELETE' });
}

/**
 * Handle API errors and show user-friendly messages
 * @param {Error} error - The error object
 * @param {string} context - The context of the error (e.g., 'fetching users')
 * @returns {string} - User-friendly error message
 */
export function handleApiError(error, context = '') {
  console.error(`API Error in ${context}:`, error);
  
  if (error.message.includes('HTTP 401')) {
    return 'Authentication failed. Please log in again.';
  }
  
  if (error.message.includes('HTTP 403')) {
    return 'Access denied. You do not have permission to perform this action.';
  }
  
  if (error.message.includes('HTTP 404')) {
    return 'The requested resource was not found.';
  }
  
  if (error.message.includes('HTTP 422')) {
    return 'Validation error. Please check your input and try again.';
  }
  
  if (error.message.includes('HTTP 500')) {
    return 'Server error. Please try again later.';
  }
  
  if (error.message.includes('Invalid response format')) {
    return 'Server returned an invalid response. Please try again.';
  }
  
  return error.message || `An error occurred while ${context}. Please try again.`;
}
