<template>
  <AppModal
    :model-value="modelValue"
    title="Імпорт товарів з CSV"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div class="space-y-4">
      <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
        Завантажте файл CSV для імпорту нових товарів або оновлення існуючих.
        Формат файлу повинен точно збігатися з експортованим файлом.
      </p>

      <!-- Drag & Drop Area -->
      <div
        :class="[
          'border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-colors',
          dragOver
            ? 'border-primary bg-primary-50/50 dark:bg-primary-950/20'
            : 'border-gray-300 hover:border-primary dark:border-gray-700 dark:hover:border-primary',
        ]"
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="handleFileDrop"
        @click="triggerFileInput"
      >
        <input
          ref="fileInputRef"
          type="file"
          class="hidden"
          accept=".csv"
          @change="handleFileSelect"
        >
        <svg
          class="mx-auto h-12 w-12 text-gray-400"
          stroke="currentColor"
          fill="none"
          viewBox="0 0 48 48"
        >
          <path
            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        <p class="mt-2 text-sm font-bold text-gray-700 dark:text-gray-300">
          Перетягніть файл сюди або натисніть для вибору
        </p>
        <p class="text-xs text-gray-400 mt-1">
          Тільки файли .csv
        </p>
        <p
          v-if="selectedFile"
          class="mt-2 text-xs font-bold text-emerald-500"
        >
          Обрано: {{ selectedFile.name }} ({{
            (selectedFile.size / 1024).toFixed(1)
          }}
          KB)
        </p>
      </div>

      <!-- Import instructions / Format description -->
      <div
        class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-150 dark:border-gray-700 space-y-2"
      >
        <h5 class="text-xs font-black uppercase text-gray-500">
          Опис колонок CSV файлу:
        </h5>
        <ul
          class="text-[11px] text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside font-medium"
        >
          <li>
            <strong class="text-gray-800 dark:text-gray-200">ID</strong>:
            Числовий ID для оновлення наявного товару (залишити пустим для
            нового товару).
          </li>
          <li>
            <strong class="text-gray-800 dark:text-gray-200">Назва (UK)</strong>
            та
            <strong class="text-gray-800 dark:text-gray-200">Назва (EN)</strong>: Обов'язкові текстові поля.
          </li>
          <li>
            <strong class="text-gray-800 dark:text-gray-200">Категорія</strong>:
            Назва категорії (буде підібрано автоматично з існуючих).
          </li>
          <li>
            <strong class="text-gray-800 dark:text-gray-200">Бренд</strong>:
            Назва бренду (буде підібрано автоматично з існуючих).
          </li>
          <li>
            <strong class="text-gray-800 dark:text-gray-200">SKU / Ціна / Кількість</strong>: Формат:
            <code
              class="bg-gray-200 dark:bg-gray-800 px-1 py-0.5 rounded font-mono text-gray-700 dark:text-gray-300"
            >sku1 (price, stock) | sku2 (price2, stock2)</code>.
          </li>
          <li>
            <strong class="text-gray-800 dark:text-gray-200">Статус</strong>:
            <code class="font-mono text-gray-700 dark:text-gray-300">active</code>,
            <code class="font-mono text-gray-700 dark:text-gray-300">draft</code>
            або
            <code class="font-mono text-gray-700 dark:text-gray-300">hidden</code>.
          </li>
          <li>
            <strong class="text-gray-800 dark:text-gray-200">Гаряча</strong> та
            <strong class="text-gray-800 dark:text-gray-200">Рекомендовано</strong>:
            <code class="font-mono text-gray-700 dark:text-gray-300">Так</code>
            або
            <code class="font-mono text-gray-700 dark:text-gray-300">Ні</code>.
          </li>
        </ul>
      </div>

      <!-- Import Progress / Info -->
      <div
        v-if="importingStatus"
        class="p-3 bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-300 rounded-xl text-xs font-bold animate-pulse"
      >
        {{ importingStatus }}
      </div>
    </div>

    <template #footer>
      <AppButton
        variant="secondary"
        class="mr-2"
        :disabled="importing"
        @click="$emit('update:modelValue', false)"
      >
        Скасувати
      </AppButton>
      <AppButton
        variant="primary"
        :disabled="!selectedFile || importing"
        @click="processImport"
      >
        <span v-if="importing">Імпортування...</span>
        <span v-else>Почати імпорт</span>
      </AppButton>
    </template>
  </AppModal>
</template>

<script setup>
import { ref } from "vue";
import api from "@/shared/services/api/apiClient";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  products: {
    type: Array,
    required: true,
  },
  categories: {
    type: Array,
    required: true,
  },
  brands: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "refresh"]);

const dragOver = ref(false);
const selectedFile = ref(null);
const fileInputRef = ref(null);
const importing = ref(false);
const importingStatus = ref("");

const triggerFileInput = () => {
  fileInputRef.value?.click();
};

const handleFileSelect = (event) => {
  const files = event.target.files;
  if (files && files.length > 0) {
    selectedFile.value = files[0];
  }
};

const handleFileDrop = (event) => {
  dragOver.value = false;
  const files = event.dataTransfer.files;
  if (files && files.length > 0) {
    selectedFile.value = files[0];
  }
};

const processImport = async () => {
  if (!selectedFile.value) return;

  importing.value = true;
  importingStatus.value = "Зчитування файлу...";

  const reader = new FileReader();
  reader.onload = async (e) => {
    try {
      const text = e.target.result;
      const parsedRows = parseCSV(text);
      if (parsedRows.length <= 1) {
        alert("Помилка: Файл порожній або містить лише заголовок.");
        importing.value = false;
        importingStatus.value = "";
        return;
      }

      const dataRows = parsedRows.slice(1);
      let successCount = 0;
      let errorCount = 0;

      for (let i = 0; i < dataRows.length; i++) {
        const row = dataRows[i];
        if (row.length < 3 || !row[1] || !row[2]) {
          continue;
        }

        importingStatus.value = `Імпорт товару ${i + 1} з ${dataRows.length}...`;

        const existingProduct = row[0]
          ? props.products.find((p) => p.id === parseInt(row[0]))
          : null;

        const catName = row[3] ? row[3].trim().toLowerCase() : "";
        const matchedCategory = props.categories.find(
          (c) =>
            (c.nameUk || "").toLowerCase() === catName ||
            (c.nameEn || "").toLowerCase() === catName,
        );
        const categoryId = matchedCategory
          ? matchedCategory.id
          : props.categories[0]?.id || null;

        const brandName = row[4] ? row[4].trim().toLowerCase() : "";
        const matchedBrand = props.brands.find(
          (b) => (b.name || "").toLowerCase() === brandName,
        );
        const brandId = matchedBrand ? matchedBrand.id : null;

        const parsedVars = parseVariants(row[5]);
        const finalVariants = parsedVars.map((parsedVar) => {
          const matchedVar = existingProduct
            ? existingProduct.variants.find((v) => v.sku === parsedVar.sku)
            : null;
          if (matchedVar) {
            return {
              id: matchedVar.id,
              sku: parsedVar.sku,
              price: parsedVar.price,
              oldPrice: matchedVar.oldPrice,
              stock: parsedVar.stock,
              weight: matchedVar.weight,
              images: matchedVar.images || [
                {
                  url: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop",
                  isPrimary: true,
                },
              ],
              attributes: matchedVar.attributes || [],
            };
          }
          return parsedVar;
        });

        if (finalVariants.length === 0) {
          finalVariants.push({
            id: null,
            sku: "temp-sku-" + Math.floor(Math.random() * 100000),
            price: 0,
            oldPrice: null,
            stock: 0,
            weight: null,
            images: [
              {
                url: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop",
                isPrimary: true,
              },
            ],
            attributes: [],
          });
        }

        const payload = {
          id: existingProduct ? existingProduct.id : null,
          nameUk: row[1].trim(),
          nameEn: row[2].trim(),
          descriptionUk: existingProduct ? existingProduct.descriptionUk : "",
          descriptionEn: existingProduct ? existingProduct.descriptionEn : "",
          categoryId: categoryId,
          brandId: brandId,
          status: ["active", "draft", "hidden"].includes(
            row[6]?.trim().toLowerCase(),
          )
            ? row[6].trim().toLowerCase()
            : "active",
          isHot: (row[7] || "").trim().toLowerCase() === "так",
          isRecommended: (row[8] || "").trim().toLowerCase() === "так",
          variants: finalVariants,
        };

        try {
          if (payload.id) {
            await api.put(`/admin/products/${payload.id}`, payload);
          } else {
            await api.post("/admin/products", payload);
          }
          successCount++;
        } catch (e) {
          console.error(`Failed to import row ${i + 1}:`, e);
          errorCount++;
        }
      }

      alert(
        `Імпорт завершено. Успішно: ${successCount}, Помилок: ${errorCount}`,
      );
      emit("refresh");
      emit("update:modelValue", false);
      selectedFile.value = null;
    } catch (e) {
      console.error("Failed to parse CSV:", e);
      alert("Помилка при обробці CSV файлу.");
    } finally {
      importing.value = false;
      importingStatus.value = "";
    }
  };
  reader.readAsText(selectedFile.value);
};

const parseVariants = (str) => {
  if (!str) return [];
  return str.split("|").map((vStr) => {
    vStr = vStr.trim();
    const parenIndex = vStr.indexOf("(");
    if (parenIndex === -1) {
      return {
        id: null,
        sku: vStr,
        price: 0,
        oldPrice: null,
        stock: 0,
        weight: null,
        images: [
          {
            url: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop",
            isPrimary: true,
          },
        ],
        attributes: [],
      };
    }
    const sku = vStr.substring(0, parenIndex).trim();
    const details = vStr.substring(parenIndex + 1, vStr.lastIndexOf(")"));
    const parts = details.split(",");
    let price = 0;
    let stock = 0;
    if (parts[0]) {
      price = parseFloat(parts[0].replace(/[^\d.]/g, "")) || 0;
    }
    if (parts[1]) {
      stock = parseInt(parts[1].replace(/[^\d]/g, ""), 10) || 0;
    }
    return {
      id: null,
      sku,
      price,
      oldPrice: null,
      stock,
      weight: null,
      images: [
        {
          url: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop",
          isPrimary: true,
        },
      ],
      attributes: [],
    };
  });
};

const parseCSV = (text) => {
  const lines = [];
  let row = [""];
  let inQuotes = false;

  for (let i = 0; i < text.length; i++) {
    const c = text[i];
    const next = text[i + 1];

    if (c === '"') {
      if (inQuotes && next === '"') {
        row[row.length - 1] += '"';
        i++;
      } else {
        inQuotes = !inQuotes;
      }
    } else if (c === "," && !inQuotes) {
      row.push("");
    } else if ((c === "\r" || c === "\n") && !inQuotes) {
      if (c === "\r" && next === "\n") {
        i++;
      }
      lines.push(row);
      row = [""];
    } else {
      row[row.length - 1] += c;
    }
  }
  if (row.length > 1 || row[0] !== "") {
    lines.push(row);
  }
  return lines;
};
</script>
