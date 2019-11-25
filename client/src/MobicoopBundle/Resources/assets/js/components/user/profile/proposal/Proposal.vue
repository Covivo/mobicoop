<template>
  <v-card>
    <proposal-header
      :is-driver="isDriver"
      :is-passenger="isPassenger"
      :is-pausable="isRegular"
      :is-archived="isArchived"
      :has-accepted-ask="hasAtLeastOneAcceptedAsk"
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
      hasAtLeastOneAsk: false,
      hasAtLeastOneAcceptedAsk: false,
      lastMessageId: null
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
  },
  mounted () {
    this.checkAsks();
  },
  methods: {
    checkAsks () {
      let hasAtLeastOneAsk = false;
      let hasAtLeastOneAcceptedAsk = false;
      let lastMessageId = -1;

      // check offers of outward
      if (this.proposal.outward && this.proposal.outward.matchingOffers) {
        this.proposal.outward.matchingOffers.forEach(offer => {
          let asks = offer.asks;
          let asksLength = asks.length;
          if (asksLength > 0) {
            hasAtLeastOneAsk = true;
            asks.forEach(ask => {
              let askHistories = ask.askHistories;
              let askHistoriesLength = askHistories.length;
              lastMessageId = askHistories[askHistoriesLength - 1].message > lastMessageId ? askHistories[askHistoriesLength - 1].message.id : lastMessageId;
              // todo: passer le statut a 4 après merge de l'update
              if (ask.status === 3) hasAtLeastOneAcceptedAsk = true;
            })
          }
        })
      }
      // check requests of outward
      if (this.proposal.outward && this.proposal.outward.matchingRequests) {
        this.proposal.outward.matchingRequests.forEach(request => {
          let asks = request.asks;
          let asksLength = asks.length;
          if (asksLength > 0) {
            hasAtLeastOneAsk = true;
            request.asks.forEach(ask => {
              let askHistories = ask.askHistories;
              let askHistoriesLength = askHistories.length;
              lastMessageId = askHistories[askHistoriesLength - 1].message > lastMessageId ? askHistories[askHistoriesLength - 1].message.id : lastMessageId;
              // todo: passer le statut a 4 après merge de l'update
              if (ask.status === 3) hasAtLeastOneAcceptedAsk = true;
            })
          }
        })
      }
      // check offers of return
      if (this.proposal.return && this.proposal.return.matchingOffers) {
        this.proposal.return.matchingOffers.forEach(offer => {
          if (offer.asks.length > 0) {
            let asks = offer.asks;
            let asksLength = asks.length;
            if (asksLength > 0) {
              hasAtLeastOneAsk = true;
              asks.forEach(ask => {
                let askHistories = ask.askHistories;
                let askHistoriesLength = askHistories.length;
                lastMessageId = askHistories[askHistoriesLength - 1].message > lastMessageId ? askHistories[askHistoriesLength - 1].message.id : lastMessageId;
                // todo: passer le statut a 4 après merge de l'update
                if (ask.status === 3) hasAtLeastOneAcceptedAsk = true;
              })
            }
          }
        })
      }
      // check requests of outward
      if (this.proposal.return && this.proposal.return.matchingRequests) {
        this.proposal.return.matchingRequests.forEach(request => {
          if (request.asks.length > 0) {
            let asks = request.asks;
            let asksLength = asks.length;
            if (asksLength > 0) {
              hasAtLeastOneAsk = true;
              asks.forEach(ask => {
                let askHistories = ask.askHistories;
                let askHistoriesLength = askHistories.length;
                lastMessageId = askHistories[askHistoriesLength - 1].message > lastMessageId ? askHistories[askHistoriesLength - 1].message.id : lastMessageId;
                // todo: passer le statut a 4 après merge de l'update
                if (ask.status === 3) hasAtLeastOneAcceptedAsk = true;
              })
            }
          }
        })
      }
      this.hasAtLeastOneAsk = hasAtLeastOneAsk;
      this.hasAtLeastOneAcceptedAsk = hasAtLeastOneAcceptedAsk;
      this.lastMessageId = lastMessageId;
    },
    proposalDeleted(id) {
      this.$emit('proposal-deleted', id)
    }
  }
}
</script>

<style scoped lang="scss">
</style>