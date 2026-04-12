<script setup>
import DashboardSidebar from '@/components/dashboard/DashboardSidebar.vue'
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue'
import StatsGrid from '@/components/dashboard/StatsGrid.vue'
import PackagesTable from '@/components/dashboard/PackagesTable.vue'
import BookingsTable from '@/components/dashboard/BookingsTable.vue'
import MessagesPanel from '@/components/dashboard/MessagesPanel.vue'
import ReviewsPanel from '@/components/dashboard/ReviewsPanel.vue'
import OffersPanel from '@/components/dashboard/OffersPanel.vue'
</script>

<template>
  <div class="dashboard-layout">

    <DashboardSidebar />

    <div class="dashboard-content">
      <DashboardHeader />

      <StatsGrid />

      <PackagesTable />
      <OffersPanel />

      <BookingsTable />
      <MessagesPanel />
      <ReviewsPanel />
    </div>

  </div>
</template>