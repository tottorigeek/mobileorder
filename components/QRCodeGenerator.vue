<template>
  <div class="qr-code-generator">
    <div v-if="qrCodeDataUrl" class="qr-code-display">
      <img :src="qrCodeDataUrl" :alt="`QR Code for ${shopCode} - Table ${tableNumber}`" class="qr-code-image" />
      <div class="qr-code-info">
        <p class="text-sm text-gray-600 mt-2">店舗: {{ shopCode }}</p>
        <p class="text-sm text-gray-600">テーブル: {{ tableNumber }}</p>
      </div>
      <button
        @click="downloadQRCode"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        QRコードをダウンロード
      </button>
    </div>
    <div v-else class="qr-code-loading">
      <p>QRコードを生成中...</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import QRCode from 'qrcode'

interface Props {
  shopCode: string
  tableNumber: string
  size?: number
}

const props = withDefaults(defineProps<Props>(), {
  size: 256
})

const qrCodeDataUrl = ref<string | null>(null)

onMounted(async () => {
  await generateQRCode()
})

const generateQRCode = async () => {
  try {
    const tableStore = useTableStore()
    const url = tableStore.generateQRCodeUrl(props.shopCode, props.tableNumber)
    
    const dataUrl = await QRCode.toDataURL(url, {
      width: props.size,
      margin: 2,
      color: {
        dark: '#000000',
        light: '#FFFFFF'
      }
    })
    
    qrCodeDataUrl.value = dataUrl
  } catch (error) {
    console.error('QRコードの生成に失敗しました:', error)
  }
}

const downloadQRCode = () => {
  if (!qrCodeDataUrl.value) return
  
  const link = document.createElement('a')
  link.href = qrCodeDataUrl.value
  link.download = `qr-code-${props.shopCode}-table-${props.tableNumber}.png`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

watch(() => [props.shopCode, props.tableNumber], async () => {
  await generateQRCode()
})
</script>

<style scoped>
.qr-code-generator {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.qr-code-display {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.qr-code-image {
  max-width: 100%;
  height: auto;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 8px;
  background: white;
}

.qr-code-info {
  text-align: center;
  margin-top: 8px;
}

.qr-code-loading {
  padding: 20px;
  text-align: center;
  color: #6b7280;
}
</style>

