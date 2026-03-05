<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import PromocodeBlock from '../../PromocodeBlock.vue'
import Button from '../../ui/Button.vue'
import Modal from '../../ui/Modal.vue'

import { useAuthStore } from '~/stores/auth'
import CurrencySelector from "~/components/auth/CurrencySelector.vue";

const props = defineProps<{ show: boolean; social: string }>()
const emit = defineEmits<{
  (e: 'close'): void
  (e: 'openShowModalAuth'): void
}>()

const { t } = useI18n()
const auth = useAuthStore()
const isLoading = ref(false)

const closeShow = () => emit('close')
const openShowModalAuth = () => {
  emit('close')
  emit('openShowModalAuth')
}

const startAuth = async () => {
  if (isLoading.value) return
  isLoading.value = true

  try {
    await auth.authWithSocial(props.social)
  } finally {
    isLoading.value = false
  }
}
</script>


<template>
	<Modal :show="props.show" @close="closeShow" class="modal">
		<div class="header">
			<Button variant="outline" @click="openShowModalAuth">{{
				t('back')
			}}</Button>
			<h3>{{ t(`register_${props.social}`) }}</h3>
		</div>

		<div class="form">
      <CurrencySelector />
			<PromocodeBlock />
		</div>

		<Button :loading="isLoading" @click="startAuth" variant="primary" full-width>{{ t('register') }}</Button>
		<p class="text">
			{{ t('rulesConfirm') }}
			<span>{{ t('rulesAndTerms') }}</span>
		</p>
	</Modal>
</template>

<style lang="scss" scoped>
.modal {
	.header {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 32px;

		h3 {
			color: #fff;
			font: 600 16px/100% 'Montserrat', sans-serif;
		}

		button {
			padding: 8px 16px;
			font-size: 12px;
			border-radius: 10px;
			width: 71px;
			height: 32px;
		}
	}

	.form {
		display: flex;
		flex-direction: column;
		gap: 16px;
		margin-bottom: 32px;
	}

	.text {
		color: rgba(208, 182, 255, 0.5);
		font: 500 12px/100% 'Montserrat', sans-serif;
		text-align: center;
		margin-top: 8px;

		span {
			color: #ff6c2d;
		}
	}
}
</style>
