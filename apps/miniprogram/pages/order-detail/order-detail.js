const { request } = require('../../utils/request');

Page({
  data: {
    order: {}
  },
  onLoad(query) {
    const orderNo = query.order_no;
    request(`/api/v1/orders/${orderNo}`).then((res) => {
      if (res.code === 0) {
        this.setData({ order: res.data });
      }
    });
  },
  mockPay() {
    const orderNo = this.data.order.order_no;
    request('/api/v1/pay/wechat/prepay', 'POST', { order_no: orderNo }).then((res) => {
      if (res.code === 0) {
        wx.showToast({ title: '支付成功', icon: 'success' });
      }
    });
  }
});
