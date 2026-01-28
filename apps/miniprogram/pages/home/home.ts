import { request } from '../../utils/request';

Page({
  data: {
    products: [] as any[]
  },
  onLoad() {
    this.fetchProducts();
  },
  fetchProducts() {
    request<any[]>('/api/v1/products').then((res) => {
      if (res.code === 0) {
        this.setData({ products: res.data });
      }
    });
  },
  goDetail(e: WechatMiniprogram.BaseEvent) {
    const id = (e.currentTarget as any).dataset.id;
    wx.navigateTo({ url: `/pages/product-detail/product-detail?id=${id}` });
  }
});
