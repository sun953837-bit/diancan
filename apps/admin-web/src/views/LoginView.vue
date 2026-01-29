<template>
  <div class="login">
    <h2>管理员登录</h2>
    <el-input v-model="account" placeholder="账号" />
    <el-input v-model="password" type="password" placeholder="密码" />
    <el-button type="primary" @click="login">登录</el-button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import client from '../api/client';

const account = ref('admin');
const password = ref('Admin@123');
const router = useRouter();

const login = async () => {
  const res = await client.post('/api/v1/admin/login', { account: account.value, password: password.value });
  if (res.data.code === 0) {
    localStorage.setItem('admin_token', res.data.data.token);
    router.push('/dashboard');
  }
};
</script>

<style scoped>
.login {
  max-width: 360px;
  margin: 120px auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
</style>
