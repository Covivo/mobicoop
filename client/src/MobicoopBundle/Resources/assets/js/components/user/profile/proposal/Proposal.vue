<template>
  <v-card>
    <proposal-header
      :is-driver="isDriver"
      :is-passenger="isPassenger"
      :is-pausable="isRegular"
    />
    
    <v-card-text v-if="isRegular">
      <proposal-content-regular :proposal="proposal" />
    </v-card-text>
      
    <v-card-text v-else>
      <proposal-content-punctual :proposal="proposal" />
    </v-card-text>

    <v-divider />
      
    <v-card-actions class="py-0">
      <proposal-footer
        :seats="proposal.outward.criteria.seats"
        :price="proposal.outward.criteria.price"
        :carpool-requests="proposal.outward.matchingRequests.length"
      />
    </v-card-actions>
  </v-card>
</template>

<script>
import ProposalHeader from '@components/user/profile/proposal/ProposalHeader.vue';
import ProposalFooter from '@components/user/profile/proposal/ProposalFooter.vue';
import ProposalContentRegular from '@components/user/profile/proposal/ProposalContentRegular.vue';
import ProposalContentPunctual from '@components/user/profile/proposal/ProposalContentPunctual.vue';
  
export default {
  components: {
    ProposalHeader,
    ProposalFooter,
    ProposalContentRegular,
    ProposalContentPunctual
  },
  props: {
    proposal: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    return {
      
    }
  },
  computed: {
    isDriver () {
      return !!this.proposal.outward.criteria.driver;
    },
    isPassenger () {
      return !!this.proposal.outward.criteria.passenger;
    },
    isRegular () {
      return this.proposal.outward.criteria.frequency === 2;
    },
    hasReturn () {
      return this.proposal.return;
    }
  }
}
</script>

<style scoped lang="scss">
</style>