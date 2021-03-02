<template>
  <span>
    <v-icon>{{ icon }}</v-icon>
    <v-chip
      v-if="line"
      :color="lineColor"
    >
      {{ line }}
    </v-chip>    
    <v-icon v-if="arrow">
      mdi-arrow-right
    </v-icon>
    <span v-if="showCompany && company">
      <br>
      {{ company }}
    </span>
  </span>
</template>
<script>
export default {
  props:{
    ptLeg: {
      type: Object,
      default:null
    },
    arrow:{
      type: Boolean,
      default:true
    },
    showCompany:{
      type: Boolean,
      default: false
    }
  },
  data(){
    return {
    }
  },
  computed: {
    icon(){
      return this.ptLeg.travelMode.mdiIcon;
    },
    line(){
      return (this.ptLeg.pTLine && this.ptLeg.pTLine.number) ? this.ptLeg.pTLine.number : null;
    },
    company(){
      return (this.ptLeg.pTLine && this.ptLeg.pTLine.pTCompany) ? this.ptLeg.pTLine.pTCompany.name : null;
    },
    lineColor(){
      return (this.ptLeg.pTLine && this.ptLeg.pTLine.color) ? this.ptLeg.pTLine.color : 'secondary';
    }
  }
}
</script>