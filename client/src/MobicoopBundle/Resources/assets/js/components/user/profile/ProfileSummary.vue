<template>
  <div>
    <div v-if="loading">
      <v-skeleton-loader
        class="mx-auto"
        type="card"
      />
    </div>
    <div v-else-if="profileSummary">
      <!-- Avatar -->
      <v-img
        aspect-ratio="2"
        :src="profileSummary.avatar"
      />
      <v-card-title>
        <v-row
          dense
        >
          <v-col
            cols="12"
            class="text-center"
          >
            {{ profileSummary.givenName }} {{ profileSummary.shortFamilyName }}
          </v-col>
        </v-row>
      </v-card-title>
      <v-card-text>
        <v-row
          dense
        >
          <v-col
            cols="12"
            class="text-center"
          >
            {{ profileSummary.age }} ans<br>
            <span v-if="profileSummary && profileSummary.phoneDisplay == 2">{{ profileSummary.telephone }}</span>
          </v-col>
        </v-row>
        <v-row
          dense
        >
          <v-col
            cols="12"
            class="text-left"
          >
            <v-icon>mdi-car</v-icon> {{ $t('infos.carpoolRealized') }} : {{ profileSummary.carpoolRealized }}<br>
            <v-icon>mdi-chat-processing</v-icon> {{ $t('infos.answerPct') }} : {{ profileSummary.answerPct }}%
          </v-col>
        </v-row>
      </v-card-text>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import Translations from "@translations/components/user/profile/ProfileSummary.json";
export default {
  i18n: {
    messages: Translations,
  },
  props:{
    userId:{
      type:Number,
      default: null
    }
  },
  data(){
    return{
      profileSummary:null,
      loading:true
    }
  },
  mounted(){
    this.getProfileSummary()
  },
  methods:{
    getProfileSummary(){
      axios.post(this.$t('getProfileSummaryUri'),{'userId':this.userId})
        .then(response => {
          //console.log(response.data);
          this.profileSummary = response.data;
          this.loading = false;
        })
        .catch(function (error) {
          console.error(error);
        });
    }
  }
}
</script>