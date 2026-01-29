const BASE_URL = 'http://localhost:8080';

/**
 * 统一请求封装
 */
const request = (url, method = 'GET', data) => {
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
        resolve(res.data);
      },
      fail: (err) => {
        wx.showToast({ title: '网络异常', icon: 'none' });
        reject(err);
      }
    });
  });
};

module.exports = {
  request
};
