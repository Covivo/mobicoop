<template>
  <div>
    <v-list-item
      class="pa-0"
      v-on="carpooler.id != defaultId ? { click: displayProfileDialog } : {}"
    >
      <!--Carpooler avatar-->
      <ProfileAvatar
        :avatar="carpooler.avatars[1]"
        :experienced="carpooler.experienced"
        :minimized="true"
      />
      <!--Carpooler data-->
      <v-list-item-content>
        <v-list-item-title class="font-weight-bold">
          {{ carpooler.givenName }} {{ carpooler.shortFamilyName }}
        </v-list-item-title>

        <v-list-item-title v-if="ageDisplay && birthdateDisplay">
          {{ age }}
        </v-list-item-title>
      </v-list-item-content>
      <span
        v-if="carpooler.verifiedIdentity !== null && carpooler.verifiedIdentity !== undefined"
        class="mr-2"
      >
        <verified-identity
          :verified-identity="carpooler.verifiedIdentity"
        />
      </span>
      <v-img
        v-if="hasBadges && gamificationActive"
        src="/images/badge.png"
        contain
        max-width="25"
      >
        <p class="caption text-center mt-1 primary--text font-weight-bold">
          {{ carpooler.numberOfBadges }}
        </p>
      </v-img>
    </v-list-item>
    <PopupPublicProfile
      :carpooler-id="carpooler.id"
      :carpooler-name="getCarpoolerName"
      :show-profile-dialog="showProfileDialog"
      @dialogClosed="showProfileDialog = false"
    />
  </div>
</template>

<script>
import moment from "moment";
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
import PopupPublicProfile from "@components/user/profile/PopupPublicProfile";
import VerifiedIdentity from "@components/user/profile/VerifiedIdentity";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/utilities/CarpoolerSummary/";

const DEFAULT_USER_ID = 999999999999;

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  components:{
    ProfileAvatar,
    PopupPublicProfile,
    VerifiedIdentity
  },
  props: {
    carpooler: {
      type: Object,
      required: true
    },
    ageDisplay: {
      type: Boolean,
      default: false
    },
    birthdateDisplay: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      defaultId: DEFAULT_USER_ID,
      locale: localStorage.getItem("X-LOCALE"),
      hasBadges: this.carpooler.numberOfBadges>0 ? true : false,
      showProfileDialog: false
    }
  },
  computed: {
    age (){
      if (this.carpooler.age) {
        return this.carpooler.age + ' ' + this.$t("birthYears");
      } else {
        return null;
      }
    },
    gamificationActive(){
      return this.$store.getters['g/isActive'];
    },
    getCarpoolerName() {
      return `${this.carpooler.givenName}${this.carpooler.shortFamilyName ? ' '+this.carpooler.shortFamilyName : ''}`;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    displayProfileDialog() {
      this.showProfileDialog = true;
    }
  },
}
</script>

<style scoped>

</style>
