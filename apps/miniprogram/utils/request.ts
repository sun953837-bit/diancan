const BASE_URL = 'http://localhost:8080';

export interface ApiResponse<T> {
  code: number;
  msg: string;
  data: T;
  traceId: string;
}

/**
 * 统一请求封装
 */
export const request = <T>(url: string, method: 'GET' | 'POST' = 'GET', data?: any): Promise<ApiResponse<T>> => {
  return new Promise((resolve, reject) => {
    const token = wx.getStorageSync('token');
    wx.request({
      url: `${BASE_URL}${url}`,
      method,
      data,
      header: {
        'Content-Type': 'application/json',
        Authorization: token ? `Bearer ${token}` : ''
      },
      success: (res) => {
        resolve(res.data as ApiResponse<T>);
      },
      fail: (err) => {
        wx.showToast({ title: '网络异常', icon: 'none' });
        reject(err);
      }
    });
  });
};
