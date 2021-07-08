<!--* Copyright (c) 2018, MOBICOOP. All rights reserved.-->
<!--* This project is dual licensed under AGPL and proprietary licence.-->
<!--***************************-->
<!--*    This program is free software: you can redistribute it and/or modify-->
<!--*    it under the terms of the GNU Affero General Public License as-->
<!--*    published by the Free Software Foundation, either version 3 of the-->
<!--*    License, or (at your option) any later version.-->
<!--*-->
<!--*    This program is distributed in the hope that it will be useful,-->
<!--*    but WITHOUT ANY WARRANTY; without even the implied warranty of-->
<!--*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the-->
<!--*    GNU Affero General Public License for more details.-->
<!--*-->
<!--*    You should have received a copy of the GNU Affero General Public License-->
<!--*    along with this program.  If not, see <gnu.org/licenses>.-->
<!--***************************-->
<!--*    Licence MOBICOOP described in the file-->
<!--*    LICENSE-->
<!--**************************-->

<template>
  <v-container
    id="scroll-target"
    style="max-height: 500px"
    class="overflow-y-auto"
    fluid
  >
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
        align="center"
      >
        <h1>{{ $t('title') }}</h1>
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col class="col-4">
        <v-alert type="info">
          <p>{{ $t("almostDone") }}</p>
          <p>{{ $t("validationMailSend") }}</p>
          <p v-html="$t('canValid')" />
        </v-alert>
      </v-col>
    </v-row>
    <v-row
      justify="center"
      align="center"
    >
      <v-col class="col-4 text-center">
        <v-alert
          v-if="errorDisplay!==''"
          type="error"
          class="text-left"
        >
          {{ errorDisplay }}
        </v-alert>
        <v-form
          id="formLoginValidation"
          ref="form"
          v-model="valid"
          lazy-validation
          :action="emailValidation ? $t('urlEmailValidation') : $t('urlSignUpValidation') "
          method="POST"
        >
          <input
            type="hidden"
            name="email"
            :value="email"
          >

          <v-text-field
            id="token"
            v-model="token"
            :rules="tokenRules"
            :label="$t('token')"
            name="emailToken"
            required
            @change="token = token.replace(/\s/g, '')"
          />
          <v-btn
            :disabled="!valid"
            color="secondary"
            type="submit"
            rounded
            @click="validate"
          >
            {{ $t('validate') }}
          </v-btn>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/SignUpValidation/";

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
    emailValidation: {
      type: Boolean,
      default: false
    },
    urlToken: {
      type: String,
      default: ""
    },
    urlEmail:{
      type: String,
      default: ""
    },
    error: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      valid:true,
      token:this.urlToken,
      email:this.urlEmail,
      tokenRules: [
        v => !!v || this.$t("tokenRequired")
      ],
      errorDisplay: "",
    }
  },
  mounted(){
    if(this.error !== "") this.treatErrorMessage(this.error);
  },
  methods:{
    validate(){
      event.preventDefault();
      if (this.$refs.form.validate()) {
        document.getElementById("formLoginValidation").submit();
      }
    },
    treatErrorMessage(error) {

      if (error === "Bad credentials.") {
        this.errorDisplay = this.$t("errorCredentials");
        this.loading = false;
      }
      else{
        this.errorDisplay = this.$t(error);
        this.loading = false;
      }
    }
  },


};
</script>
