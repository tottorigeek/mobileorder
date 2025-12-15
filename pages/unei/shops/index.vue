<template>
    <div class="space-y-6">
      <!-- ヘッダー -->
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">店舗管理</h2>
          <p class="text-sm sm:text-base text-gray-600">システム全体の店舗を管理します</p>
        </div>
        <button
          @click="showAddModal = true"
          class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target font-semibold flex items-center justify-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          店舗を追加
        </button>
      </div>

      <!-- ローディング -->
      <div v-if="shopStore.isLoading" class="text-center py-12 sm:py-16">
        <div class="inline-block animate-spin rounded-full h-12 w-12 sm:h-16 sm:w-16 border-4 border-green-200 border-t-green-600"></div>
        <p class="mt-4 text-gray-500 font-medium text-sm sm:text-base">読み込み中...</p>
      </div>

      <!-- 店舗一覧（0件時） -->
      <div v-else-if="filteredShops.length === 0" class="text-center py-12 sm:py-16 bg-white rounded-2xl shadow-lg px-4">
        <svg class="w-16 h-16 sm:w-20 sm:h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <p class="text-gray-500 font-medium text-base sm:text-lg mb-2">
          店舗が見つかりません
        </p>
        <p class="text-gray-400 text-xs sm:text-sm">
          フィルター条件を変更するか、新しい店舗を追加してください
        </p>
      </div>

      <div v-else class="space-y-4">
        <!-- フィルター -->
        <div class="bg-white p-4 sm:p-5 rounded-xl shadow-md flex flex-col sm:flex-row gap-4 sm:items-end">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
              オーナーで絞り込み
            </label>
            <select
              v-model="selectedOwnerId"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
            >
              <option value="">すべてのオーナー</option>
              <option
                v-for="owner in ownerOptions"
                :key="owner.id"
                :value="owner.id"
              >
                {{ owner.name }} <span v-if="owner.email">({{ owner.email }})</span>
              </option>
            </select>
          </div>

          <div class="flex-1 min-w-[200px]">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
              店舗名で検索
            </label>
            <input
              v-model="shopSearchKeyword"
              type="search"
              list="shop-name-suggestions"
              placeholder="例: レストラン, カフェ, shop001 など"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
            />
            <datalist id="shop-name-suggestions">
              <option
                v-for="shop in shopSuggestions"
                :key="shop.id"
                :value="shop.name"
              >
                {{ shop.code }}
              </option>
            </datalist>
          </div>
        </div>

        <!-- 店舗一覧 -->
        <div
          v-for="shop in filteredShops"
          :key="shop.id"
          class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-green-300 w-full max-w-full overflow-hidden"
        >
          <!-- ヘッダー（折りたたみトリガー） -->
          <button
            type="button"
            class="w-full px-4 sm:px-6 py-3 flex items-center justify-between rounded-t-xl hover:bg-gray-50"
            @click="toggleShopOpen(shop.id)"
          >
            <div class="flex items-start gap-3 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mb-1">
                  <h3 class="text-lg sm:text-xl font-bold text-gray-900 break-words">{{ shop.name }}</h3>
                  <span :class="shop.isActive ? 'px-2 sm:px-3 py-1 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-full text-xs font-semibold shadow-md whitespace-nowrap' : 'px-2 sm:px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold whitespace-nowrap'">
                    {{ shop.isActive ? 'アクティブ' : '無効' }}
                  </span>
                </div>
                <p class="text-[11px] sm:text-xs text-gray-500 truncate">
                  コード: {{ shop.code }} ・ {{ shop.address || '住所未設定' }}
                </p>
              </div>
            </div>
            <svg
              class="w-5 h-5 text-gray-400 transform transition-transform duration-200 flex-shrink-0 mr-1"
              :class="isShopOpen(shop.id) ? 'rotate-180' : ''"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- 本文（折りたたみ可能エリア） -->
          <div
            v-if="isShopOpen(shop.id)"
            class="px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 rounded-b-xl"
          >
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mt-3">
              <div class="flex-1 min-w-0">
                <div class="flex items-start gap-3 mb-3">
                  <!-- もともとのアイコンはヘッダーに移動したため空の余白調整用 -->
                  <div class="w-10 h-10 sm:w-12 sm:h-12 flex-shrink-0 hidden lg:block" />
                  <div class="space-y-1 text-xs sm:text-sm text-gray-600">
                    <p class="flex items-start gap-2 break-words">
                      <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                      </svg>
                      <span>コード: {{ shop.code }}</span>
                    </p>
                    <p v-if="shop.description" class="flex items-start gap-2 break-words">
                      <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                      </svg>
                      <span>{{ shop.description }}</span>
                    </p>
                    <p v-if="shop.address" class="flex items-start gap-2 break-words">
                      <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      <span>{{ shop.address }}</span>
                    </p>
                    <div v-if="shop.owners && shop.owners.length > 0" class="flex items-start gap-2 mt-2 flex-wrap">
                      <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      <span class="font-medium">オーナー:</span>
                      <span v-for="(owner, index) in shop.owners" :key="owner.id" class="break-words">
                        {{ owner.name }}
                        <span v-if="owner.email" class="text-gray-500">({{ owner.email }})</span>
                        <span v-if="index < shop.owners.length - 1" class="text-gray-400">, </span>
                      </span>
                    </div>
                    <p v-else-if="shop.owner" class="flex items-start gap-2 mt-2 break-words">
                      <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      <span><span class="font-medium">オーナー:</span> {{ shop.owner.name }}<span v-if="shop.owner.email" class="text-gray-500"> ({{ shop.owner.email }})</span></span>
                    </p>
                    <p v-else class="text-gray-400 mt-2 flex items-center gap-2">
                      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      オーナー未設定
                    </p>
                  </div>
                  
                  <!-- 売上情報 & 注文ステータス -->
                  <div class="mt-4 pt-4 border-t border-gray-200 space-y-4">
                    <!-- 売上情報 -->
                    <div>
                      <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        売上情報
                      </h4>
                      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-3">
                        <div class="bg-teal-50 p-2 sm:p-3 rounded-lg border border-teal-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1">直近1時間</p>
                          <p class="text-sm sm:text-lg font-bold text-teal-700 break-all">¥{{ getShopSales(shop.id, '1hour').toLocaleString() }}</p>
                        </div>
                        <div class="bg-green-50 p-2 sm:p-3 rounded-lg border border-green-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1">本日</p>
                          <p class="text-sm sm:text-lg font-bold text-green-700 break-all">¥{{ getShopSales(shop.id, 'today').toLocaleString() }}</p>
                        </div>
                        <div class="bg-blue-50 p-2 sm:p-3 rounded-lg border border-blue-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1">昨日</p>
                          <p class="text-sm sm:text-lg font-bold text-blue-700 break-all">¥{{ getShopSales(shop.id, 'yesterday').toLocaleString() }}</p>
                        </div>
                        <div class="bg-purple-50 p-2 sm:p-3 rounded-lg border border-purple-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1">7日間</p>
                          <p class="text-sm sm:text-lg font-bold text-purple-700 break-all">¥{{ getShopSales(shop.id, '7days').toLocaleString() }}</p>
                        </div>
                        <div class="bg-orange-50 p-2 sm:p-3 rounded-lg border border-orange-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1">30日間</p>
                          <p class="text-sm sm:text-lg font-bold text-orange-700 break-all">¥{{ getShopSales(shop.id, '30days').toLocaleString() }}</p>
                        </div>
                      </div>
                    </div>

                    <!-- 注文ステータス -->
                    <div>
                      <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                        注文ステータス
                      </h4>
                      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-3">
                        <div class="bg-yellow-50 p-2 sm:p-3 rounded-lg border border-yellow-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1">受付待ち</p>
                          <p class="text-sm sm:text-lg font-bold text-yellow-700">
                            {{ getShopOrderStats(shop.id).pending }}
                          </p>
                        </div>
                        <div class="bg-blue-50 p-2 sm:p-3 rounded-lg border border-blue-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1">調理中</p>
                          <p class="text-sm sm:text-lg font-bold text-blue-700">
                            {{ getShopOrderStats(shop.id).cooking }}
                          </p>
                        </div>
                        <div class="bg-emerald-50 p-2 sm:p-3 rounded-lg border border-emerald-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1">提供済み</p>
                          <p class="text-sm sm:text-lg font-bold text-emerald-700">
                            {{ getShopOrderStats(shop.id).completed }}
                          </p>
                        </div>
                        <div class="bg-rose-50 p-2 sm:p-3 rounded-lg border border-rose-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1">キャンセル</p>
                          <p class="text-sm sm:text-lg font-bold text-rose-700">
                            {{ getShopOrderStats(shop.id).cancelled }}
                          </p>
                        </div>
                        <div class="bg-red-50 p-2 sm:p-3 rounded-lg border border-red-200">
                          <p class="text-[10px] sm:text-xs text-gray-600 mb-1 flex items-center gap-1">
                            異常長時間
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-red-500 text-white text-[10px]">
                              !
                            </span>
                          </p>
                          <p class="text-[11px] sm:text-xs font-semibold text-red-700">
                            {{ formatDuration(getShopOrderStats(shop.id).maxPendingMinutes) }}
                          </p>
                        </div>
                      </div>

                      <!-- 個別注文の経過時間（上位） -->
                      <div
                        v-if="getShopOrderDetails(shop.id).length > 0"
                        class="mt-3 border-t border-dashed border-gray-200 pt-3 space-y-1.5"
                      >
                        <p class="text-[10px] sm:text-xs text-gray-500 font-medium">
                          個別注文の経過時間（最大5件）
                        </p>
                        <div
                          v-for="order in getShopOrderDetails(shop.id)"
                          :key="order.id"
                          class="flex items-center justify-between text-[11px] sm:text-xs px-2 py-1 rounded-md cursor-pointer hover:ring-1 hover:ring-green-400 transition"
                          :class="order.isAlert ? 'bg-red-50 text-red-700' : 'bg-gray-50 text-gray-700'"
                          @click="openOrderDetail(order.id)"
                        >
                          <div class="flex items-center gap-2 min-w-0">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white border text-[10px] font-semibold">
                              {{ order.tableNumber || '-' }}
                            </span>
                            <span class="truncate">
                              #{{ order.orderNumber }}
                              <span class="ml-1 text-[10px]" :class="order.isAlert ? 'text-red-600' : 'text-gray-500'">
                                （{{ order.statusLabel }}）
                              </span>
                            </span>
                          </div>
                          <span class="ml-2 font-semibold whitespace-nowrap">
                            {{ formatDuration(order.elapsedMinutes) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 lg:ml-4 lg:flex-shrink-0">
                <button
                  @click="handleGoToShopDashboard(shop)"
                  class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg text-sm font-semibold touch-target"
                >
                  ダッシュボード
                </button>
                <NuxtLink
                  :to="`/unei/shops/${shop.id}/edit`"
                  class="w-full sm:w-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-300 shadow-md hover:shadow-lg text-sm font-semibold text-center touch-target"
                >
                  編集
                </NuxtLink>
                <button
                  @click="handleDeleteShop(shop)"
                  :class="[
                    'w-full sm:w-auto px-4 py-2 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm font-semibold touch-target',
                    (shop.owners && shop.owners.length > 0) || shop.owner
                      ? 'bg-gray-200 text-gray-600 hover:bg-gray-300'
                      : 'bg-gradient-to-r from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700'
                  ]"
                >
                  削除
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 追加モーダル -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showAddModal = false"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
          <h3 class="text-base sm:text-lg font-semibold mb-4">店舗を追加</h3>

          <form @submit.prevent="handleAddShop" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                店舗コード <span class="text-red-500">*</span>
              </label>
              <input
                v-model="newShop.code"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="shop001"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                店舗名 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="newShop.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="店舗名を入力"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                説明
              </label>
              <textarea
                v-model="newShop.description"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                rows="3"
                placeholder="店舗の説明を入力"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                住所
              </label>
              <input
                v-model="newShop.address"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="住所を入力"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                電話番号
              </label>
              <input
                v-model="newShop.phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="03-1234-5678"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                最大テーブル数
              </label>
              <input
                v-model.number="newShop.maxTables"
                type="number"
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="20"
              />
            </div>

            <div v-if="addError" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
              {{ addError }}
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-end">
              <button
                type="button"
                @click="showAddModal = false"
                class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors touch-target"
              >
                キャンセル
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
              >
                {{ isSubmitting ? '追加中...' : '追加' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- 注文詳細モーダル -->
    <div
      v-if="selectedOrder"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4"
      @click.self="closeOrderDetail"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-4 sm:px-6 py-3 border-b">
          <div>
            <p class="text-xs text-gray-500 mb-0.5">注文詳細</p>
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">
              #{{ selectedOrder.orderNumber }}
            </h3>
          </div>
          <button
            type="button"
            class="p-1.5 rounded-full hover:bg-gray-100"
            @click="closeOrderDetail"
          >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="px-4 sm:px-6 py-4 space-y-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <p class="text-xs text-gray-500">テーブル</p>
              <p class="font-semibold text-gray-900">No. {{ selectedOrder.tableNumber || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">ステータス</p>
              <p class="font-semibold" :class="selectedOrderStatusColor">
                {{ selectedOrderStatusLabel }}
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-500">注文時間</p>
              <p class="font-medium text-gray-900">
                {{ formatDateTime(selectedOrder.createdAt) }}
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-500">経過時間</p>
              <p class="font-semibold text-gray-900">
                {{ formatDuration(selectedOrderElapsedMinutes) }}
              </p>
            </div>
          </div>

          <div class="border-t pt-3">
            <p class="text-xs text-gray-500 mb-2">注文内容</p>
            <div v-if="selectedOrder.items && selectedOrder.items.length > 0" class="space-y-2">
              <div
                v-for="item in selectedOrder.items"
                :key="item.menuId + '-' + item.menuName"
                class="flex items-center justify-between text-xs sm:text-sm"
              >
                <div class="flex flex-col">
                  <span class="font-medium text-gray-900">{{ item.menuName }}</span>
                  <span class="text-[11px] text-gray-500">x {{ item.quantity }}</span>
                </div>
                <span class="font-semibold text-gray-900">
                  ¥{{ (item.price * item.quantity).toLocaleString() }}
                </span>
              </div>
            </div>
            <p v-else class="text-xs text-gray-500">注文内容はありません</p>
          </div>

          <div class="border-t pt-3 flex items-center justify-between text-sm">
            <span class="text-gray-600 font-medium">合計金額</span>
            <span class="text-lg font-bold text-green-600">
              ¥{{ selectedOrder.totalAmount.toLocaleString() }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import { useAuthStore } from '~/stores/auth'
import { useOrderStore } from '~/stores/order'
import type { Shop } from '~/types/multi-shop'
import type { Order, OrderStatus } from '~/types'

definePageMeta({
  layout: 'company'
})

const shopStore = useShopStore()
const authStore = useAuthStore()
const orderStore = useOrderStore()

const showAddModal = ref(false)
const isSubmitting = ref(false)
const addError = ref('')

// 店舗ごとの開閉状態（デフォルトは全店舗オープン）
const openShopIds = ref<string[]>([])

// 注文詳細モーダル
const selectedOrder = ref<Order | null>(null)
const selectedOrderElapsedMinutes = ref(0)

const selectedOrderStatusLabel = computed(() => {
  if (!selectedOrder.value) return ''
  const map: Record<OrderStatus, string> = {
    pending: '受付待ち',
    accepted: '受付済み',
    cooking: '調理中',
    completed: '完了',
    cancelled: 'キャンセル',
    checkout_pending: '会計待ち'
  }
  return map[selectedOrder.value.status as OrderStatus] || selectedOrder.value.status
})

const selectedOrderStatusColor = computed(() => {
  if (!selectedOrder.value) return ''
  const status = selectedOrder.value.status as OrderStatus
  switch (status) {
    case 'pending':
      return 'text-yellow-700'
    case 'accepted':
    case 'cooking':
      return 'text-blue-700'
    case 'completed':
      return 'text-emerald-700'
    case 'checkout_pending':
      return 'text-orange-700'
    case 'cancelled':
      return 'text-rose-700'
    default:
      return 'text-gray-700'
  }
})

// オーナー絞り込み用
const selectedOwnerId = ref<string>('')

// 店舗名検索用
const shopSearchKeyword = ref<string>('')

type OwnerOption = {
  id: string
  name: string
  email?: string | null
}

// 店舗データからオーナー候補を抽出
const ownerOptions = computed<OwnerOption[]>(() => {
  const map = new Map<string, OwnerOption>()

  for (const shop of shopStore.shops as any[]) {
    if (shop.owners && Array.isArray(shop.owners)) {
      for (const owner of shop.owners) {
        if (!owner?.id) continue
        if (!map.has(owner.id)) {
          map.set(owner.id, {
            id: String(owner.id),
            name: owner.name || owner.username || '名称未設定',
            email: owner.email ?? null
          })
        }
      }
    } else if (shop.owner && shop.owner.id) {
      const owner = shop.owner
      if (!map.has(owner.id)) {
        map.set(owner.id, {
          id: String(owner.id),
          name: owner.name || owner.username || '名称未設定',
          email: owner.email ?? null
        })
      }
    }
  }

  return Array.from(map.values()).sort((a, b) => a.name.localeCompare(b.name, 'ja'))
})

// オーナーで絞り込んだ店舗一覧
const filteredShops = computed<Shop[]>(() => {
  const targetId = selectedOwnerId.value
  const keyword = shopSearchKeyword.value.trim().toLowerCase()

  return (shopStore.shops as any[]).filter(shop => {
    // オーナー絞り込み
    if (targetId) {
      let matchOwner = false
      if (shop.owners && Array.isArray(shop.owners)) {
        matchOwner = shop.owners.some((owner: any) => String(owner.id) === targetId)
      } else if (shop.owner && shop.owner.id) {
        matchOwner = String(shop.owner.id) === targetId
      }
      if (!matchOwner) return false
    }

    // 店舗名・コード検索
    if (keyword) {
      const name = (shop.name || '').toLowerCase()
      const code = (shop.code || '').toLowerCase()
      if (!name.includes(keyword) && !code.includes(keyword)) {
        return false
      }
    }

    return true
  }) as Shop[]
})

// 店舗カードの開閉制御
const isShopOpen = (shopId: string) => {
  // 初期状態: openShopIds が空のときは全店舗オープン扱い
  if (openShopIds.value.length === 0) return true
  return openShopIds.value.includes(shopId)
}

const toggleShopOpen = (shopId: string) => {
  // まだ初期状態（全てオープン）の場合は、まず全店舗IDをセットしたうえでトグル
  if (openShopIds.value.length === 0) {
    openShopIds.value = (shopStore.shops as Shop[]).map(s => s.id)
  }
  if (openShopIds.value.includes(shopId)) {
    openShopIds.value = openShopIds.value.filter(id => id !== shopId)
  } else {
    openShopIds.value.push(shopId)
  }
}

// オートコンプリート用の店舗候補
const shopSuggestions = computed<Shop[]>(() => {
  // 店舗数が多い場合に備えて、最大30件までに制限
  return (shopStore.shops as Shop[]).slice(0, 30)
})

// 店舗ごとの注文ステータス集計と経過時間
const getShopOrderStats = (shopId: string) => {
  const now = new Date()
  const shopOrders = orderStore.orders.filter(order => order.shopId === shopId)

  let pending = 0
  let cooking = 0
  let completed = 0
  let cancelled = 0
  let maxPendingMinutes = 0

  for (const order of shopOrders) {
    switch (order.status) {
      case 'pending':
        pending++
        break
      case 'accepted':
      case 'cooking':
        cooking++
        break
      case 'completed':
        completed++
        break
      case 'cancelled':
      case 'checkout_pending':
        cancelled++
        break
    }

    // 未完了系の注文について、最長の経過時間を計算（分）
    if (order.status !== 'completed' && order.status !== 'cancelled') {
      const createdAt = order.createdAt instanceof Date ? order.createdAt : new Date(order.createdAt)
      const diffMs = now.getTime() - createdAt.getTime()
      const diffMinutes = Math.floor(diffMs / 60000)
      if (diffMinutes > maxPendingMinutes) {
        maxPendingMinutes = diffMinutes
      }
    }
  }

  return {
    pending,
    cooking,
    completed,
    cancelled,
    maxPendingMinutes
  }
}

// 店舗ごとの個別注文詳細（経過時間順 上位5件）
const getShopOrderDetails = (shopId: string) => {
  const now = new Date()
  const activeStatuses: OrderStatus[] = ['pending', 'accepted', 'cooking', 'checkout_pending']

  return orderStore.orders
    .filter(order => order.shopId === shopId && activeStatuses.includes(order.status as OrderStatus))
    .map(order => {
      const createdAt = order.createdAt instanceof Date ? order.createdAt : new Date(order.createdAt)
      const diffMs = now.getTime() - createdAt.getTime()
      const diffMinutes = Math.floor(diffMs / 60000)

      const statusLabelMap: Record<OrderStatus, string> = {
        pending: '受付待ち',
        accepted: '受付済み',
        cooking: '調理中',
        completed: '完了',
        cancelled: 'キャンセル',
        checkout_pending: '会計待ち'
      }

      const isAlert = diffMinutes >= 15 // 例えば15分以上を「要注意」とする

      return {
        id: order.id,
        orderNumber: order.orderNumber,
        tableNumber: order.tableNumber,
        status: order.status as OrderStatus,
        statusLabel: statusLabelMap[order.status as OrderStatus] || order.status,
        elapsedMinutes: diffMinutes,
        isAlert
      }
    })
    .sort((a, b) => b.elapsedMinutes - a.elapsedMinutes)
    .slice(0, 5)
}

// 経過時間（分）を「XX分」「X時間YY分」の形式に変換
const formatDuration = (minutes: number) => {
  if (!minutes || minutes <= 0) return 'なし'
  if (minutes < 60) return `${minutes}分`
  const hours = Math.floor(minutes / 60)
  const rest = minutes % 60
  if (rest === 0) return `${hours}時間`
  return `${hours}時間${rest}分`
}

// 日時を見やすい形式にフォーマット
const formatDateTime = (value: Date | string) => {
  const date = value instanceof Date ? value : new Date(value)
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  const hh = String(date.getHours()).padStart(2, '0')
  const mm = String(date.getMinutes()).padStart(2, '0')
  return `${y}/${m}/${d} ${hh}:${mm}`
}

// 注文詳細モーダル制御
const openOrderDetail = (orderId: string) => {
  const order = orderStore.orders.find(o => o.id === orderId)
  if (!order) return

  selectedOrder.value = order

  const now = new Date()
  const createdAt = order.createdAt instanceof Date ? order.createdAt : new Date(order.createdAt)
  const diffMs = now.getTime() - createdAt.getTime()
  selectedOrderElapsedMinutes.value = Math.floor(diffMs / 60000)
}

const closeOrderDetail = () => {
  selectedOrder.value = null
  selectedOrderElapsedMinutes.value = 0
}

const newShop = ref({
  code: '',
  name: '',
  description: '',
  address: '',
  phone: '',
  maxTables: 20
})

const { handleLogout } = useAuthCheck()

// 店舗別売上計算
const getShopSales = (shopId: string, period: '1hour' | 'today' | 'yesterday' | '7days' | '30days'): number => {
  const shopOrders = orderStore.orders.filter(order => order.shopId === shopId && order.status === 'completed')
  
  if (shopOrders.length === 0) return 0
  
  const now = new Date()
  let startDate: Date
  let endDate: Date = new Date(now)
  
  switch (period) {
    case '1hour':
      startDate = new Date(now)
      startDate.setHours(startDate.getHours() - 1)
      endDate = new Date(now)
      break
    case 'today':
      startDate = new Date(now)
      startDate.setHours(0, 0, 0, 0)
      endDate = new Date(now)
      endDate.setHours(23, 59, 59, 999)
      break
    case 'yesterday':
      startDate = new Date(now)
      startDate.setDate(startDate.getDate() - 1)
      startDate.setHours(0, 0, 0, 0)
      endDate = new Date(startDate)
      endDate.setHours(23, 59, 59, 999)
      break
    case '7days':
      startDate = new Date(now)
      startDate.setDate(startDate.getDate() - 7)
      startDate.setHours(0, 0, 0, 0)
      endDate = new Date(now)
      endDate.setHours(23, 59, 59, 999)
      break
    case '30days':
      startDate = new Date(now)
      startDate.setDate(startDate.getDate() - 30)
      startDate.setHours(0, 0, 0, 0)
      endDate = new Date(now)
      endDate.setHours(23, 59, 59, 999)
      break
  }
  
  return shopOrders
    .filter(order => {
      const orderDate = new Date(order.createdAt)
      return orderDate >= startDate && orderDate <= endDate
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
}

const handleAddShop = async () => {
  isSubmitting.value = true
  addError.value = ''
  
  try {
    // TODO: 店舗追加APIの実装が必要
    // await shopStore.createShop(newShop.value)
    alert('店舗追加機能は実装予定です')
    showAddModal.value = false
    newShop.value = {
      code: '',
      name: '',
      description: '',
      address: '',
      phone: '',
      maxTables: 20
    }
  } catch (error: any) {
    addError.value = error?.data?.error || '店舗の追加に失敗しました'
  } finally {
    isSubmitting.value = false
  }
}

const handleGoToShopDashboard = async (shop: Shop) => {
  try {
    // 店舗をストアに設定
    shopStore.setCurrentShop(shop)
    // 店舗ダッシュボードに遷移
    await navigateTo('/shop/dashboard')
  } catch (error) {
    console.error('店舗ダッシュボードへの遷移に失敗しました:', error)
    alert('店舗ダッシュボードへの遷移に失敗しました')
  }
}

const handleDeleteShop = async (shop: Shop) => {
  // オーナーが存在する場合は削除を拒否
  const hasOwners = (shop.owners && shop.owners.length > 0) || shop.owner
  if (hasOwners) {
    alert(`店舗「${shop.name}」にはオーナーが設定されています。\n削除するには、まずオーナーを解除してください。`)
    return
  }
  
  // 確認ダイアログ
  if (!confirm(`店舗「${shop.name}」を削除しますか？\nこの操作は取り消せません。`)) {
    return
  }
  
  try {
    await shopStore.deleteShop(shop.id)
    alert('店舗を削除しました')
  } catch (error: any) {
    const errorMessage = error?.data?.error || error?.message || '店舗の削除に失敗しました'
    alert(errorMessage)
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/unei/login')
    return
  }

  // 店舗一覧を取得
  await shopStore.fetchShops()
  
  // 全店舗の注文を取得（売上計算用）
  try {
    const shopIds = shopStore.shops.map(s => s.id)
    if (shopIds.length > 0) {
      await orderStore.fetchOrders(undefined, undefined, shopIds)
    }
  } catch (error) {
    console.error('注文データの取得に失敗しました:', error)
  }
})
</script>

