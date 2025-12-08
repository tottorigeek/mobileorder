/**
 * APIベースURLを正規化するヘルパー関数
 * 末尾のスラッシュを削除して、URLの構築時にスラッシュの重複を防ぐ
 */
export const useApiBase = () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase
  
  // 末尾のスラッシュを削除
  const normalizedBase = apiBase.replace(/\/+$/, '')
  
  /**
   * APIエンドポイントのURLを構築
   * @param path エンドポイントのパス（先頭のスラッシュは不要）
   * @returns 完全なURL
   */
  const buildUrl = (path: string): string => {
    // パスの先頭のスラッシュを削除
    const normalizedPath = path.replace(/^\/+/, '')
    return `${normalizedBase}/${normalizedPath}`
  }
  
  return {
    apiBase: normalizedBase,
    buildUrl
  }
}

