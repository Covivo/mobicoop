<template>
  <div>
    <v-toolbar flat>
      <v-toolbar-title>
        <a href="/"><v-img
          class="logo"
          src="/images/logo.png"
          alt="Mobicoop"
        /></a>
      </v-toolbar-title>
      <v-spacer />
      <div
        v-if="user"
      >
        <v-btn
          text
          :href="$t('navBar.buttons.messages.route')"
        >
          {{ $t('navBar.buttons.messages.label') }}
        </v-btn>
        <v-btn
          text
          :href="$t('navBar.buttons.myProfile.route')"
        >
          {{ $t('navBar.buttons.myProfile.label') }}
        </v-btn>
        <v-btn
          text
          :href="$t('navBar.buttons.logOut.route')"
        >
          {{ $t('navBar.buttons.logOut.label') }}
        </v-btn>
      </div>
      <div v-else>
        <v-btn
          text
          :href="$t('navBar.buttons.signup.route')"
        >
          {{ $t('navBar.buttons.signup.label') }}
        </v-btn>
        <v-btn
          text
          :href="$t('navBar.buttons.logIn.route')"
        >
          {{ $t('navBar.buttons.logIn.label') }}
        </v-btn>
      </div>
      <v-btn
        rounded
        :href="$t('navBar.buttons.shareAnAd.route')"
      >
        {{ $t('navBar.buttons.shareAnAd.label') }}
      </v-btn>
    </v-toolbar>
  </div>
</template>
<script>
export default {
  props: {
    user: {
      type: Object,
      default: null
    }
  }
}
</script>