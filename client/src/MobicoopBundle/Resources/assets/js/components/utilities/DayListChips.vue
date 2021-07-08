<template>
  <div>
    <v-chip
      :color="monColor"
      class="mr-1 justify-center"
      :disabled="monDisabled || disabled"
      @click="clickDay(daysList[0])"
    >
      {{ dayName(daysList[0]) }}
    </v-chip>
    <v-chip
      :color="tueColor"
      class="mr-1 justify-center"
      :disabled="tueDisabled || disabled"
      @click="clickDay(daysList[1])"
    >
      {{ dayName(daysList[1]) }}
    </v-chip>
    <v-chip
      :color="wedColor"
      class="mr-1 justify-center"
      :disabled="wedDisabled || disabled"
      @click="clickDay(daysList[2])"
    >
      {{ dayName(daysList[2]) }}
    </v-chip>
    <v-chip
      :color="thuColor"
      class="mr-1 justify-center"
      :disabled="thuDisabled || disabled"
      @click="clickDay(daysList[3])"
    >
      {{ dayName(daysList[3]) }}
    </v-chip>
    <v-chip
      :color="friColor"
      class="mr-1 justify-center"
      :disabled="friDisabled || disabled"
      @click="clickDay(daysList[4])"
    >
      {{ dayName(daysList[4]) }}
    </v-chip>
    <v-chip
      :color="satColor"
      class="mr-1 justify-center"
      :disabled="satDisabled || disabled"
      @click="clickDay(daysList[5])"
    >
      {{ dayName(daysList[5]) }}
    </v-chip>
    <v-chip
      :color="sunColor"
      class="mr-1 justify-center"
      :disabled="sunDisabled || disabled"
      @click="clickDay(daysList[6])"
    >
      {{ dayName(daysList[6]) }}
    </v-chip>
  </div>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/DayListChips/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    monActive:{
      type: Boolean,
      default: false
    },
    tueActive:{
      type: Boolean,
      default: false
    },
    wedActive:{
      type: Boolean,
      default: false
    },
    thuActive:{
      type: Boolean,
      default: false
    },
    friActive:{
      type: Boolean,
      default: false
    },
    satActive:{
      type: Boolean,
      default: false
    },
    sunActive:{
      type: Boolean,
      default: false
    },
    monDisabled:{
      type: Boolean,
      default: false
    },
    tueDisabled:{
      type: Boolean,
      default: false
    },
    wedDisabled:{
      type: Boolean,
      default: false
    },
    thuDisabled:{
      type: Boolean,
      default: false
    },
    friDisabled:{
      type: Boolean,
      default: false
    },
    satDisabled:{
      type: Boolean,
      default: false
    },
    sunDisabled:{
      type: Boolean,
      default: false
    },
    longDaysName:{
      type: Boolean,
      default: false
    },
    colorActive:{
      type: String,
      default: "primary"
    },
    colorInactive:{
      type: String,
      default: "primary lighten-3"
    },
    colorDisabled:{
      type: String,
      default: "grey"
    },
    clickable:{
      type:Boolean,
      default:true
    },
    isOutward:{
      type:Boolean,
      default:true
    },
    disabled:{
      type:Boolean,
      default:false
    }
  },
  data() {
    return {
      daysList: this.$t('days.list'),
      mon:(this.monActive) ? true : false,
      tue:(this.tueActive) ? true : false,
      wed:(this.wedActive) ? true : false,
      thu:(this.thuActive) ? true : false,
      fri:(this.friActive) ? true : false,
      sat:(this.satActive) ? true : false,
      sun:(this.sunActive) ? true :false,
      daysListUpdated: {"mon": null, "tue": null, "wed": null, "thu":null, "fri":null, "sat": null, "sun": null, "isOutward": null} 
    }
    
  },
  computed:{
    monColor() { if(this.monDisabled) { return this.colorDisabled }else if(this.mon) {return this.colorActive}else {return this.colorInactive}},
    tueColor() { if(this.tueDisabled) { return this.colorDisabled }else if(this.tue) {return this.colorActive}else {return this.colorInactive}},
    wedColor() { if(this.wedDisabled) { return this.colorDisabled }else if(this.wed) {return this.colorActive}else {return this.colorInactive}},
    thuColor() { if(this.thuDisabled) { return this.colorDisabled }else if(this.thu) {return this.colorActive}else {return this.colorInactive}},
    friColor() { if(this.friDisabled) { return this.colorDisabled }else if(this.fri) {return this.colorActive}else {return this.colorInactive}},
    satColor() { if(this.satDisabled) { return this.colorDisabled }else if(this.sat) {return this.colorActive}else {return this.colorInactive}},
    sunColor() { if(this.sunDisabled) { return this.colorDisabled }else if(this.sun) {return this.colorActive}else {return this.colorInactive}}
  },
  watch:{
    monActive(newValue){(newValue) ? this.mon = true : this.mon = false;},
    tueActive(newValue){(newValue) ? this.tue = true : this.tue = false;},
    wedActive(newValue){(newValue) ? this.wed = true : this.wed = false;},
    thuActive(newValue){(newValue) ? this.thu = true : this.thu = false;},
    friActive(newValue){(newValue) ? this.fri = true : this.fri = false;},
    satActive(newValue){(newValue) ? this.sat = true : this.sat = false;},
    sunActive(newValue){(newValue) ? this.sun = true : this.sun = false;}
  },
  methods:{
    dayName(day){
      return (this.longDaysName) ? this.$t('days.'+day+'.longName') : this.$t('days.'+day+'.shortName');
    },
    clickDay(day){
      if(this.clickable){
        switch(day){
        case this.daysList[0]:
          this.mon = !this.mon;
          break;
        case this.daysList[1]:
          this.tue = !this.tue;
          break;
        case this.daysList[2]:
          this.wed = !this.wed;
          break;
        case this.daysList[3]:
          this.thu = !this.thu;
          break;
        case this.daysList[4]:
          this.fri = !this.fri;
          break;
        case this.daysList[5]:
          this.sat = !this.sat;
          break;
        case this.daysList[6]:
          this.sun = !this.sun;
          break;
        }
      }
      this.emitEvent();
    },
    emitEvent: function() {
      this.daysListUpdated["mon"] = this.monDisabled ? 0 : this.mon ? 1 : 2;
      this.daysListUpdated["tue"] = this.tueDisabled ? 0 : this.tue ? 1 : 2;
      this.daysListUpdated["wed"] = this.wedDisabled ? 0 : this.wed ? 1 : 2;
      this.daysListUpdated["thu"] = this.thuDisabled ? 0 : this.thu ? 1 : 2;
      this.daysListUpdated["fri"] = this.friDisabled ? 0 : this.fri ? 1 : 2;
      this.daysListUpdated["sat"] = this.satDisabled ? 0 : this.sat ? 1 : 2;
      this.daysListUpdated["sun"] = this.sunDisabled ? 0 : this.sun ? 1 : 2;
      this.daysListUpdated["isOutward"] = this.isOutward ? true : false;
      this.$emit("change",this.daysListUpdated);
    },
  }
}
</script>
<style lang="scss" scoped>
  .v-chip{
    width:40px;
    height:40px;
    border-radius:40px;
  }
</style>