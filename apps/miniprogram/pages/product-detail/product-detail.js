const { request } = require('../../utils/request');

Page({
  data: {
    product: {},
    selectedSku: {}
  },
  onLoad(query) {
    const id = query.id;
    request(`/api/v1/products/${id}`).then((res) => {
      if (res.code === 0) {
        const product = res.data;
        this.setData({ product, selectedSku: (product.skus && product.skus[0]) || {} });
      }
    });
  },
  addToCart() {
    wx.showToast({ title: '已加入购物车', icon: 'success' });
  }
});
