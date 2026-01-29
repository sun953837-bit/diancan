const { request } = require('../../utils/request');

Page({
  doLogin() {
    wx.login({
      success: (res) => {
        request('/api/v1/auth/wx-login', 'POST', { code: res.code }).then((resp) => {
          if (resp.code === 0) {
            wx.setStorageSync('token', resp.data.token);
            wx.showToast({ title: '登录成功', icon: 'success' });
          }
        });
      }
    });
  }
});
