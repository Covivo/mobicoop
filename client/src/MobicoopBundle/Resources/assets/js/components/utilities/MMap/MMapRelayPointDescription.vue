<template>
  <div>
    <v-card
      min-width="200"
      flat
    >
      <v-row no-gutters>
        <v-col
          cols="6"
          class="text-center"
          :class="determineColor(data.official)"
        >
          {{ $t('official') }}
          <v-icon
            v-if="determineIcon(data.official)"
            class="subtitle-2"
            :class="determineColor(data.official)"
          >
            {{ determineIcon(data.official) }}
          </v-icon>
          <span v-else>?</span>
        </v-col>
        <v-col
          cols="6"
          class="text-center"
          :class="determineColor(data.private)"
        >
          {{ $t('private') }} 
          <v-icon
            v-if="determineIcon(data.private)"
            class="subtitle-2"
            :class="determineColor(data.private)"
          >
            {{ determineIcon(data.private) }}
          </v-icon>
          <span v-else>?</span>
        </v-col>
      </v-row>
      <v-row no-gutters>
        <v-col
          cols="6"
          class="text-center"
          :class="determineColor(data.secured)"
        >
          {{ $t('secured') }} 
          <v-icon
            v-if="determineIcon(data.secured)"
            class="subtitle-2"
            :class="determineColor(data.secured)"
          >
            {{ determineIcon(data.secured) }}
          </v-icon>
          <span v-else>?</span>
        </v-col>
        <v-col
          cols="6"
          class="text-center"
          :class="determineColor(data.free)"
        >
          {{ $t('free') }} 
          <v-icon
            v-if="determineIcon(data.free)"
            class="subtitle-2"
            :class="determineColor(data.free)"
          >
            {{ determineIcon(data.free) }}
          </v-icon>
          <span v-else>?</span>
        </v-col>
      </v-row>
      <v-row no-gutters>
        <v-col>{{ $t('places') }} {{ data.places ? data.places : '?' }}<br>{{ $t('placesDisabled') }} {{ data.placesDisabled ? data.placesDisabled : '?' }}</v-col>
      </v-row>
      <v-row
        v-if="data.image"
        no-gutters
      >
        <v-col
          cols="12"
          class="text-center"
        >
          <v-img :src="data.image" />
        </v-col>
      </v-row>
    </v-card>
  </div>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MMap/MMapRelayPointDescription";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props:{
    data:{
      type: Object,
      default: null
    }
  },
  methods:{
    determineColor(value){
      if(true === value){
        return "success--text";
      }
      else if(false === value){
        return "error--text";
      }
      return "";
    },
    determineIcon(value){
      if(true === value){
        return "mdi-check-circle";
      }
      else if(false === value){
        return "mdi-close-circle";
      }
      return "";
    }    
  }
}
</script>