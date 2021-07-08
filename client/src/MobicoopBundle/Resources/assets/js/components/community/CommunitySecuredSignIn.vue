<template>
  <v-container fluid>
    <v-row
      justify="center"
    >
      <v-col
        cols="4"
        md="8"
        xl="6"
        align="center"
      >
        <h1 v-if="communityName === null">
          {{ $t('title') }}
        </h1>
        <h1 v-else>
          {{ $t('titleWithName') }} "{{ communityName }}"
        </h1>
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-col class="col-4">
        <v-alert
          v-model="error"
          type="error"
        >
          {{ $t('errors.badCredentials') }}
        </v-alert>
        <v-form
          id="formSecuredSignIn"
          ref="form"
          v-model="valid"
          lazy-validation
          :action="$t('urlSignIn', {id:communityId})"
          method="POST"
        >
          <v-text-field
            v-model="credential1"
            name="credential1"
            :label="$t('credential1')"
            :rules="credentialsRules"
            required
          />

          <v-text-field
            v-model="credential2"
            name="credential2"
            :label="$t('credential2')"
            :rules="credentialsRules"
            required
          />

          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <div
                class="text-center"
                v-on="(userId === null) ? on : {}"
              >
                <v-btn
                  :disabled="!valid || userId === null"
                  color="secondary"
                  type="submit"
                  rounded
                  @click="validate"
                >
                  {{ $t('join') }}
                </v-btn>
              </div>
            </template>
            <span>{{ $t("errors.notLogged") }}</span>
          </v-tooltip>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/community/CommunitySecuredSignIn/";

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
    communityId: {
      type: Number,
      default: null
    },
    communityName: {
      type: String,
      default: null
    },
    userId:{
      type: Number,
      default: null
    },
    error:{
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      valid:false,
      credential1:"",
      credential2:"",
      credentialsRules: [
        v => !!v || this.$t("errors.required"),
      ]
    }
  },
  methods: {
    validate() {
      event.preventDefault();
      if (this.$refs.form.validate()) {
        document.getElementById("formSecuredSignIn").submit();

      }
    },
  }
}
</script>
