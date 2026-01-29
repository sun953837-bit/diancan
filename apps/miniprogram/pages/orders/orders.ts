import { request } from '../../utils/request';

Page({
  data: {
    orders: [] as any[]
  },
  onShow() {
    request<any[]>('/api/v1/orders').then((res) => {
      if (res.code === 0) {
        this.setData({ orders: res.data });
      }
    });
  },
  goDetail(e: WechatMiniprogram.BaseEvent) {
    const orderNo = (e.currentTarget as any).dataset.order;
    wx.navigateTo({ url: `/pages/order-detail/order-detail?order_no=${orderNo}` });
  }
});
