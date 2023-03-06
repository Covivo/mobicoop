<template>
  <v-card>
    <!-- ad.driver is an empty Array if the carpooler is passenger -->
    <!-- ad.driver is an object if the carpooler is driver -->
    <!-- ad.passengers is always an array -->
    <ad-header
      :is-driver="ad.roleDriver"
      :is-passenger="ad.rolePassenger"
      :is-pausable="ad.frequency === 2"
      :is-paused="ad.paused"
      :is-archived="isArchived"
      :is-solidary-exclusive="ad.isSolidaryExclusive"
      :has-accepted-ask="!Array.isArray(ad.driver) || ad.passengers.length > 0"
      :has-ask="ad.asks"
      :ad-id="ad.id"
      @ad-deleted="adDeleted"
      @pause-ad="adPaused"
    />

    <v-card-text v-if="ad.frequency === 2">
      <ad-content-regular :ad="ad" />
    </v-card-text>

    <v-card-text v-else>
      <ad-content-punctual :ad="ad" />
    </v-card-text>

    <v-divider class="primary lighten-5" />

    <v-card-actions>
      <ad-footer
        v-if="!ad.paused"
        :id="ad.id"
        :seats="ad.roleDriver && !ad.rolePassenger ? ad.seats : null"
        :price="ad.roleDriver && !ad.rolePassenger ? ad.price : null"
        :id-message="lastMessageId"
        :nb-matchings="ad.carpoolers"
        :is-archived="isArchived"
        :communities="ad.communities ? ad.communities : []"
      />
    </v-card-actions>
  </v-card>
</template>

<script>
import AdHeader from "@components/user/profile/ad/AdHeader.vue";
import AdFooter from "@components/user/profile/ad/AdFooter.vue";
import AdContentRegular from "@components/user/profile/ad/AdContentRegular.vue";
import AdContentPunctual from "@components/user/profile/ad/AdContentPunctual.vue";

export default {
  components: {
    AdHeader,
    AdFooter,
    AdContentRegular,
    AdContentPunctual
  },
  props: {
    ad: {
      type: Object,
      default: () => {}
    },
    isArchived: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      lastMessageId: null
    };
  },
  methods: {
    adDeleted(isArchived, id, message) {
      this.$emit("ad-deleted", isArchived, id, message);
    },
    adPaused(paused) {
      this.ad.paused = paused;
    }
  }
};
</script>

<style scoped lang="scss"></style>
