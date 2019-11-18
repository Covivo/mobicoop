<template>
  <v-card>
    <proposal-header
      :is-driver="isDriver"
      :is-passenger="isPassenger"
      :is-pausable="isRegular"
      :is-archived="isArchived"
      :has-formal-ask="hasFormalAsk"
      :has-ask="hasAtLeastOneAsk"
      :proposal-id="proposal.outward ? proposal.outward.id : proposal.return ? proposal.return.id : null"
      @proposal-deleted="proposalDeleted()"
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
        :is-driver="isDriver"
        :is-passenger="isPassenger"
        :carpool-requests="proposal.outward.matchingRequests"
        :carpool-offers="proposal.outward.matchingOffers"
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
    },
    isArchived: {
      type: Boolean,
      default: false
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
    },
    hasAtLeastOneAsk () {
      let hasAtLeastOneAsk = false;
      // check offers of outward
      if (this.proposal.outward && this.proposal.outward.matchingOffers) {
        hasAtLeastOneAsk = this.proposal.outward.matchingOffers.some(offer => {
          return offer.asks.length > 0;
        })
      }
      // check requests of outward
      if (!hasAtLeastOneAsk && this.proposal.outward && this.proposal.outward.matchingRequests) {
        hasAtLeastOneAsk = this.proposal.outward.matchingRequests.some(request => {
          return request.asks.length > 0;
        })
      }
      // check offers of return
      if (!hasAtLeastOneAsk && this.proposal.return && this.proposal.return.matchingOffers) {
        hasAtLeastOneAsk = this.proposal.return.matchingOffers.some(offer => {
          return offer.asks.length > 0;
        })
      }
      // check requests of outward
      if (!hasAtLeastOneAsk && this.proposal.return && this.proposal.return.matchingRequests) {
        hasAtLeastOneAsk = this.proposal.return.matchingRequests.some(request => {
          return request.asks.length > 0;
        })
      }
      return hasAtLeastOneAsk;
    },
    hasFormalAsk () {
      return this.proposal.outward ? this.proposal.outward.formalAsk ? this.proposal.outward.formalAsk : this.proposal.return ? this.proposal.return.formalAsk : false : false;
    }
  },
  methods: {
    proposalDeleted(id) {
      this.$emit('proposal-deleted', id)
    }
  }
}
</script>

<style scoped lang="scss">
</style>