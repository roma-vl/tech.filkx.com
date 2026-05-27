<script setup>
import { ref } from 'vue';
import { store } from '@/store.js';

const activeFaqIndex = ref(null);
const faqs = [
  { q: 'How long does shipping take?', a: 'Standard shipping takes 3-5 business days. Express shipping takes 1-2 business days. Premium members enjoy free express shipping on all orders.' },
  { q: 'What is your return policy?', a: 'We offer a 30-day return policy on all hardware and electronics. Items must be in their original packaging, unused, and include all manuals/accessories.' },
  { q: 'How do I claim my product warranty?', a: 'All items come with a standard 2-year warranty. You can register claims by submitting a support ticket here, or by visiting any authorized center with your order invoice.' },
  { q: 'Can I change my delivery address after placing an order?', a: 'Addresses can be updated within 1 hour of placing your order. Once the status changes to "Shipped", we cannot redirect it.' },
];
const toggleFaq = (i) => { activeFaqIndex.value = activeFaqIndex.value === i ? null : i; };

const ticketForm = ref({ subject: '', category: 'Order Issue', message: '' });
const ticketsList = ref([
  { id: 'TK-8492', subject: 'Delay in delivery', category: 'Order Issue', date: 'May 24, 2026', status: 'In Progress', statusClass: 'text-secondary bg-secondary-container/30 border border-secondary/20' },
  { id: 'TK-3921', subject: 'Broken headphone cable', category: 'Warranty & Returns', date: 'Jan 12, 2026', status: 'Closed', statusClass: 'text-on-surface-variant bg-surface-container-high border border-outline-variant' },
]);

const submitTicket = () => {
  if (!ticketForm.value.subject || !ticketForm.value.message) {
    store.addToast('Please fill out the ticket subject and description.', 'warning');
    return;
  }
  const newId = `TK-${Math.floor(1000 + Math.random() * 9000)}`;
  ticketsList.value.unshift({
    id: newId, subject: ticketForm.value.subject, category: ticketForm.value.category,
    date: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }),
    status: 'Open', statusClass: 'text-primary bg-primary/10 border border-primary/20',
  });
  store.addToast(`Ticket ${newId} created! Our team will respond shortly.`, 'success');
  ticketForm.value.subject = '';
  ticketForm.value.message = '';
};
</script>

<template>
  <div class="space-y-gutter animate-fade">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">

      <!-- Left: FAQ + History -->
      <div class="lg:col-span-2 space-y-gutter">
        <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
          <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Frequently Asked Questions</h3>
          <div class="divide-y divide-outline-variant">
            <div v-for="(faq, idx) in faqs" :key="idx" class="py-3">
              <button @click="toggleFaq(idx)" class="w-full flex items-center justify-between text-left font-semibold text-xs text-on-surface hover:text-primary transition-colors py-1">
                <span>{{ faq.q }}</span>
                <span class="material-symbols-outlined text-[18px] transition-transform duration-300"
                      :class="activeFaqIndex === idx ? 'rotate-180 text-primary' : 'text-on-surface-variant'">expand_more</span>
              </button>
              <div v-show="activeFaqIndex === idx" class="mt-2 text-xs text-on-surface-variant leading-relaxed">{{ faq.a }}</div>
            </div>
          </div>
        </section>

        <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
          <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Support Tickets</h3>
          <div v-if="ticketsList.length > 0" class="divide-y divide-outline-variant">
            <div v-for="ticket in ticketsList" :key="ticket.id" class="py-4 first:pt-0 last:pb-0 flex flex-wrap justify-between items-center gap-4 text-xs">
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-bold text-on-surface text-sm">{{ ticket.subject }}</span>
                  <span class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant bg-surface-container-high px-2 py-0.5 rounded">{{ ticket.category }}</span>
                </div>
                <p class="text-on-surface-variant text-[11px] mt-1">Ticket ID: {{ ticket.id }} • Created: {{ ticket.date }}</p>
              </div>
              <span class="inline-block px-3 py-1 rounded-full font-bold uppercase text-[9px] tracking-wider" :class="ticket.statusClass">{{ ticket.status }}</span>
            </div>
          </div>
          <p v-else class="text-xs text-on-surface-variant italic text-center py-4">No tickets created.</p>
        </section>
      </div>

      <!-- Right: Submit Ticket -->
      <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4 h-fit">
        <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Submit a Ticket</h3>
        <form @submit.prevent="submitTicket" class="space-y-4">
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Category</label>
            <select v-model="ticketForm.category"
                    class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none">
              <option>Order Issue</option>
              <option>Warranty & Returns</option>
              <option>Technical Support</option>
              <option>Billing & Invoices</option>
            </select>
          </div>
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Subject</label>
            <input v-model="ticketForm.subject" type="text" placeholder="Brief subject description..." required
                   class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none" />
          </div>
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Message</label>
            <textarea v-model="ticketForm.message" rows="4" placeholder="How can we help you today?" required
                      class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none resize-none"></textarea>
          </div>
          <button type="submit" class="w-full bg-primary text-on-primary font-bold text-xs py-2.5 rounded-lg hover:bg-primary-container transition-all uppercase tracking-wider flex items-center justify-center gap-1.5">
            <span class="material-symbols-outlined text-[16px]">send</span> Send Ticket
          </button>
        </form>
      </section>

    </div>
  </div>
</template>

<style scoped>
.animate-fade { animation: fadeIn .25s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
</style>
