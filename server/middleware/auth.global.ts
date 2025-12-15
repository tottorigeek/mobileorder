import { defineEventHandler, getCookie, sendRedirect } from 'h3'
import { useRuntimeConfig } from '#imports'

// パスごとのアクセス制御
const publicPaths = [
  '/',
  '/staff/login',
  '/company/login',
  '/staff/forgot-password',
  '/staff/reset-password',
  '/company/reset-password'
]

export default defineEventHandler(async (event) => {
  const reqUrl = event.node.req.url || '/'
  // 静的アセットやAPIはスキップ
  if (
    reqUrl.startsWith('/_nuxt') ||
    reqUrl.startsWith('/__nuxt') ||
    reqUrl.startsWith('/_vercel') ||
    reqUrl.startsWith('/api') ||
    reqUrl.startsWith('/radish') // APIサーバー側のパスとは別。Nuxt内のパスで判断
  ) {
    return
  }

  // visitor配下はクライアントレンダリングのためスキップ
  if (reqUrl.startsWith('/visitor')) return

  // 公開パスはスキップ
  if (publicPaths.includes(reqUrl)) return

  const token = getCookie(event, 'auth_token')
  if (!token) {
    const loginPath = reqUrl.startsWith('/company') ? '/company/login' : '/staff/login'
    return sendRedirect(event, loginPath)
  }

  // トークンがあればバックエンドで検証
  try {
    const config = useRuntimeConfig()
    await $fetch(`${config.public.apiBase}/auth/me`, {
      headers: {
        Authorization: `Bearer ${token}`
      },
      retry: 0
    })
  } catch (e) {
    const loginPath = reqUrl.startsWith('/company') ? '/company/login' : '/staff/login'
    return sendRedirect(event, loginPath)
  }
})


