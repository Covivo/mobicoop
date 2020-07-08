<template>
  <div>
    <MatchingPTResultLeg
      v-for="(ptLeg,index) in ptLegs"
      :key="index"
      :pt-leg="ptLeg"
      :arrow="index == ptLegs.length-1 ? false : true"
    />
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
  }
}
</script>