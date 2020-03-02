<template>
  <v-card>
    <proposal-header
      :is-driver="isDriver"
      :is-passenger="isPassenger"
      :is-pausable="isRegular"
      :is-paused="isPaused"
      :is-archived="isArchived"
      :has-accepted-ask="hasAtLeastOneAcceptedAsk"
      :has-ask="hasAtLeastOneAsk"
      :proposal-id="proposal.outward.id"
      @proposal-deleted="proposalDeleted"
      @pause-ad="pauseAd"
    />
    
    <v-card-text v-if="isRegular">
      <proposal-content-regular :proposal="proposal" />
    </v-card-text>
      
    <v-card-text v-else>
      <proposal-content-punctual :proposal="proposal" />
    </v-card-text>

    <v-divider class="primary lighten-5" />
      
    <v-card-actions class="py-0">
      <proposal-footer
        v-if="!isPaused"
        :id="proposal.outward.id"
        :seats="(isDriver) ? proposal.outward.seatsDriver : proposal.outward.seatsPassenger"
        :price="(isDriver) ? proposal.outward.outwardDriverPrice : proposal.outward.outwardPassengerPrice"
        :id-message="lastMessageId"
        :nb-matchings="proposal.outward.results.length"
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
      lastMessageId: null,
      isPaused: this.proposal.outward.paused
    }
  },
  computed: {
    isDriver () {
      return this.proposal.outward.role === 1 || this.proposal.outward.role === 3
    },
    isPassenger () {
      return (this.proposal.outward.role === 2 || this.proposal.outward.role === 3) && this.proposal.outward.solidaryExclusive != 1
    },
    isRegular () {
      return this.proposal.outward.frequency === 2;
    },
    hasReturn () {
      return !this.proposal.outward.oneWay;
    }
  },
  mounted () {
    this.checkAsks();
  },
  methods: {
    checkAsks () {
      this.proposal.outward.results.forEach(result => {
        if (result.pendingAsk) {
          this.hasAtLeastOneAsk = true;
        }
        if (result.acceptedAsk) {
          this.hasAtLeastOneAcceptedAsk = true;
        } 
      });
    },
    proposalDeleted(isArchived, id) {
      this.$emit('proposal-deleted', isArchived, id)
    },
    pauseAd(pauseAd) {
      this.isPaused = pauseAd;
     
    }
  }
}
</script>

<style scoped lang="scss">
</style>