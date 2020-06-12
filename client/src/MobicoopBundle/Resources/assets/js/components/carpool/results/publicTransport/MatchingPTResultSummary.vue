<template>
  <div>
    <MatchingPTResultLeg
      v-for="(ptLeg,index) in ptLegs"
      :key="index"
      :pt-leg="ptLeg"
      :last="index == ptLegs.length-1 ? true : false"
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