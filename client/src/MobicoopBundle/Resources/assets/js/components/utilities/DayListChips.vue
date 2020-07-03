<template>
  <div>
    <v-chip
      :color="monColor"
      class="mr-2"
    >
      {{ dayName(daysList[0]) }}
    </v-chip>
    <v-chip
      :color="tueColor"
      class="mr-2"
    >
      {{ dayName(daysList[1]) }}
    </v-chip>
    <v-chip
      :color="wedColor"
      class="mr-2"
    >
      {{ dayName(daysList[2]) }}
    </v-chip>
    <v-chip
      :color="thuColor"
      class="mr-2"
    >
      {{ dayName(daysList[3]) }}
    </v-chip>
    <v-chip
      :color="friColor"
      class="mr-2"
    >
      {{ dayName(daysList[4]) }}
    </v-chip>
    <v-chip
      :color="satColor"
      class="mr-2"
    >
      {{ dayName(daysList[5]) }}
    </v-chip>
    <v-chip
      :color="sunColor"
      class="mr-2"
    >
      {{ dayName(daysList[6]) }}
    </v-chip>
  </div>
</template>
<script>
import Translations from "@translations/components/utilities/DayListChips.json";

export default {
  i18n: {
    messages: Translations,
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
    longDaysName:{
      type: Boolean,
      default: false
    },
    colorActive:{
      type: String,
      default: "success"
    },
    colorInactive:{
      type: String,
      default: "default"
    }
  },
  data() {
    return {
      daysList: this.$t('days.list'),
      monColor:(this.monActive) ? this.colorActive : this.colorInactive,
      tueColor:(this.tueActive) ? this.colorActive : this.colorInactive,
      wedColor:(this.wedActive) ? this.colorActive : this.colorInactive,
      thuColor:(this.thuActive) ? this.colorActive : this.colorInactive,
      friColor:(this.friActive) ? this.colorActive : this.colorInactive,
      satColor:(this.satActive) ? this.colorActive : this.colorInactive,
      sunColor:(this.sunActive) ? this.colorActive : this.colorInactive
    }
  }, 
  methods:{
    dayName(day){
      return (this.longDaysName) ? this.$t('days.'+day+'longName') : this.$t('days.'+day+'.shortName');
    }
  }
}
</script>