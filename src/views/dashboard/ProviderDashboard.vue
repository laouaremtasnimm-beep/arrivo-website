<script setup>
import DashboardSidebar from '@/components/dashboard/DashboardSidebar.vue'
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue'
import StatsGrid from '@/components/dashboard/StatsGrid.vue'
import ServicesTable from '@/components/dashboard/ServicesTable.vue'
import BookingsTable from '@/components/dashboard/BookingsTable.vue'
import MessagesPanel from '@/components/dashboard/MessagesPanel.vue'
import ReviewsPanel from '@/components/dashboard/ReviewsPanel.vue'
</script>

<template>
  <div class="dashboard-layout">

    <DashboardSidebar />

    <div class="dashboard-content">
      <DashboardHeader />

      <StatsGrid />

      <ServicesTable />

      <BookingsTable />
      <MessagesPanel />
      <ReviewsPanel />
    </div>

  </div>
</template>