<template>
  <v-card
    height="215"
    flat
  >
    <result-journey-detailed-card />
    <v-divider class="divider-width" />
    <result-user-detailed-card />
  </v-card>
</template>
<script>
import ResultJourneyDetailedCard from "./ResultJourneyDetailedCard";
import ResultUserDetailedCard from "./ResultUserDetailedCard";
export default {
  name: "ResultCard",
  components:{
    ResultJourneyDetailedCard,
    ResultUserDetailedCard
  }
}
</script>

<style scoped>
    .divider-width{
        max-width: 730px;
    }
</style>