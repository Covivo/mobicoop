<template>
  <!-- Matching results -->
  <div v-if="loading">
    <v-row
      v-for="n in 3"
      :key="n"
      class="text-left"
    >
      <v-col cols="12">
        <v-skeleton-loader
          ref="skeleton"
          type="article"
          class="mx-auto"
        />
        <v-skeleton-loader
          ref="skeleton"
          type="actions"
          class="mx-auto"
        />
      </v-col>
    </v-row>
  </div>
  <div v-else>
    <v-row 
      v-for="(result,index) in results"
      :key="index"
      justify="center"
    >
      <v-col
        cols="12"
        align="left"
      >
        <!-- Matching result -->
        <matching-result
          :result="result"
          :user="user"
          :distinguish-regular="distinguishRegular"
          :carpooler-rate="carpoolerRate"
          @carpool="carpool(result)"
        />
      </v-col>
    </v-row>
  </div>
</template>
<script>
import MatchingResult from "@components/carpool/results/MatchingResult";

export default {
  components: {
    MatchingResult,
  },
  props:{
    externalJourneys:{
      type: Boolean,
      default: false
    },
    user: {
      type:Object,
      default: null
    },
    results:{
      type:Array,
      default:null
    },
    distinguishRegular: {
      type: Boolean,
      default: false
    },
    carpoolerRate: {
      type: Boolean,
      default: true
    },
    loadingProp: {
      type: Boolean,
      default: false
    }
  },
  data(){
    return {
      loading:this.loadingProp
    }
  },
  watch:{
    loadingProp(){
      this.loading = this.loadingProp
    }
  },
  methods:{
    carpool(carpool){
      this.$emit("carpool", carpool);
    }
  }
}
</script>