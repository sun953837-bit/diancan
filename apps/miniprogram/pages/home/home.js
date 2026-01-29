const { request } = require('../../utils/request');

Page({
  data: {
    products: []
  },
  onLoad() {
    this.fetchProducts();
  },
  fetchProducts() {
    request('/api/v1/products').then((res) => {
      if (res.code === 0) {
        this.setData({ products: res.data });
      }
    });
  },
  goDetail(e) {
    const id = e.currentTarget.dataset.id;
    wx.navigateTo({ url: `/pages/product-detail/product-detail?id=${id}` });
  }
});
