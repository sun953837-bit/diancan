const { request } = require('../../utils/request');

Page({
  createOrder() {
    request('/api/v1/orders', 'POST', {
      items: [{ sku_id: 1, qty: 1 }],
      address_id: 1,
      fulfillment_type: 'SELF'
    }).then((res) => {
      if (res.code === 0) {
        wx.showToast({ title: '下单成功', icon: 'success' });
        wx.navigateTo({ url: `/pages/order-detail/order-detail?order_no=${res.data.order_no}` });
      }
    });
  }
});
