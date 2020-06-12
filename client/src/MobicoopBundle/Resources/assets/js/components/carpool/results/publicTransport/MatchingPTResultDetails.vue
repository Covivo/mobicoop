<template>
  <div>
    <v-row
      v-for="(ptLeg,index) in ptLegs"
      :key="index"
      align="center"
    >
      <v-col
        cols="2"
      >
        <MatchingPTResultLeg
          :pt-leg="ptLeg"
          :arrow="false"
        />
      </v-col>
      <v-col cols="10">
        le détail...
      </v-col>
    </v-row>
  </div>
</template>
<script>
import MatchingPTResultLeg from "@components/carpool/results/publicTransport/MatchingPTResultLeg";
export default {
  components:{
    MatchingPTResultLeg
  },
  props:{
    ptLegs: {
      type: Array,
      default:null
    }
  },  
  data(){
    return {
    }
  },
  methods: {
    icon(currentLeg){
      return currentLeg.travelMode.mdiIcon;
    }
  }
}
</script>