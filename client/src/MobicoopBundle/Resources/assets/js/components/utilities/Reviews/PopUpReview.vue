<template>
  <v-container>
    <v-tooltip bottom>
      <template v-slot:activator="{ on, attrs }">
        <v-btn
          color="primary"
          v-bind="attrs"
          small
          depressed
          fab
          class="ml-2"
          v-on="on"
          @click="dialog = true"
        >
          <v-icon>
            mdi-notebook
          </v-icon>
        </v-btn>
      </template>
      <span>{{ tooltipTxt }}</span>
    </v-tooltip>    
  
    <v-dialog
      v-model="dialog"
      width="80%"
    >
      <v-card>
        <v-row no-gutters>
          <v-col cols="12">
            <v-card-title class="headline grey lighten-2">
              {{ tooltipTxt }}
            </v-card-title>

            <v-card-text>
              {{ $t('intro.part1', {'user':reviewedName}) }}<br>{{ $t('intro.part2') }}
            </v-card-text>
          </v-col>
        </v-row>
        <v-row no-gutters>
          <v-col cols="12">
            <WriteReview
              :reviewed="reviewed"
              :reviewer="reviewer"
              @reviewLeft="reviewLeft"
            />
          </v-col>
        </v-row>
      </v-card>
    </v-dialog> 
  </v-container> 
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/Reviews/PopUpReviews";
import WriteReview from "@components/utilities/Reviews/WriteReview";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components:{
    WriteReview
  },
  props:{
    reviewer:{
      type: Object,
      default: null
    },
    reviewed:{
      type: Object,
      default: null
    },
    tooltip:{
      type: String,
      default: null
    }
  },
  data(){
    return{
      dialog:false
    }
  },
  computed:{
    tooltipTxt(){
      if(this.tooltip){
        return this.tooltip;
      }
      else{
        return this.$t('tooltip');
      }
    },
    reviewedName(){
      return this.reviewed.givenName+" "+this.reviewed.shortFamilyName;
    }
  },
  methods:{
    reviewLeft(data){
      this.$emit('reviewLeft',data);
      this.dialog = false;
    }
  }
}
</script>