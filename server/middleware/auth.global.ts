import { defineEventHandler, getCookie, sendRedirect, getRequestURL } from 'h3'
import { useRuntimeConfig } from '#imports'

// パスごとのアクセス制御（クエリ・トレーリングスラッシュを除いたパスで判定する）
const publicPaths = [
  '/',
  '/staff/login',
  '/company/login',
  '/staff/forgot-password',
  '/staff/reset-password',
  '/company/reset-password'
]

export default defineEventHandler(async (event) => {
  const url = getRequestURL(event)
  const pathname = url.pathname || '/'

  // 静的アセットや内部APIはスキップ
  if (
    pathname.startsWith('/_nuxt') ||
    pathname.startsWith('/__nuxt') ||
    pathname.startsWith('/_vercel') ||
    pathname.startsWith('/api') ||
    pathname.startsWith('/radish') // APIサーバー側のパスとは別。Nuxt内のパスで判断
  ) {
    return
  }

  // visitor配下はクライアントレンダリングのためスキップ
  if (pathname.startsWith('/visitor')) return

  // 公開パスはスキップ
  if (publicPaths.includes(pathname)) return

  const token = getCookie(event, 'auth_token')
  if (!token) {
    const loginPath = pathname.startsWith('/company') ? '/company/login' : '/staff/login'
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
    const loginPath = pathname.startsWith('/company') ? '/company/login' : '/staff/login'
    return sendRedirect(event, loginPath)
  }
})


