<template>
  <div class="mt-5 black--text">
    <div v-if="loading">
      <v-skeleton-loader
        class="mx-auto"
        type="card"
      />
    </div>
    <div v-else-if="profileSummary">
      <!-- Avatar -->
      <ProfileAvatar
        :avatar="profileSummary.avatar"
        :experienced="profileSummary.experienced"
      />
      <div
        v-if="showLinkProfile"
        class="text-center"
      >
        <a
          href="#"
          title=""
        >Voir le profil</a>
      </div>
      <div>
        <v-row
          no-gutters
          class="title"
        >
          <v-col
            cols="12"
            class="text-center"
          >
            {{ profileSummary.givenName }} {{ profileSummary.shortFamilyName }}
          </v-col>
        </v-row>
        <v-row
          no-gutters
        >
          <v-col
            cols="12"
            class="text-center body-2"
          >
            {{ profileSummary.age }} {{ $t('yearsOld') }}<br>
            <span v-if="profileSummary && profileSummary.phoneDisplay == 2">{{ profileSummary.telephone }}</span>
          </v-col>
        </v-row>
      </div>
      <div class="body-2 pa-2">
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
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
import Translations from "@translations/components/user/profile/ProfileSummary.json";
export default {
  i18n: {
    messages: Translations,
  },
  components:{
    ProfileAvatar
  },
  props:{
    userId:{
      type:Number,
      default: null
    },
    showLinkProfile:{
      type: Boolean,
      default: true
    }
  },
  data(){
    return{
      profileSummary:null,
      loading:true,
      experienced:false
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