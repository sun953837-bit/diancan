const { request } = require('../../utils/request');

Page({
  data: {
    orders: []
  },
  onShow() {
    request('/api/v1/orders').then((res) => {
      if (res.code === 0) {
        this.setData({ orders: res.data });
      }
    });
  },
  goDetail(e) {
    const orderNo = e.currentTarget.dataset.order;
    wx.navigateTo({ url: `/pages/order-detail/order-detail?order_no=${orderNo}` });
  }
});
