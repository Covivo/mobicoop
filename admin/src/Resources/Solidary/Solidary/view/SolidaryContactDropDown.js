import React, { useState } from 'react';

import DropDownButton from '../../../../components/button/DropDownButton';
import { SolidaryMessagesModal } from '../SolidaryMessagesModal';
import { SolidarySMSModal } from '../SolidarySMSModal';

const SMS_CONTACT_OPTION = 'Envoyer directement un SMS';
const MESSAGE_CONTACT_OPTION = 'Écrire vers sa messagerie';

// @TODO
// const ASKFORRESPONSE_OPTION = 'Solliciter réponse formelle';

const options = [SMS_CONTACT_OPTION, MESSAGE_CONTACT_OPTION /* @TODO: ASKFORRESPONSE_OPTION  */];

export const SolidaryContactDropDown = ({ solidaryId, solidarySolutionId, ...props }) => {
  const [contactType, setContactType] = useState(null);

  const closeModal = () => setContactType(null);

  return (
    <>
      <DropDownButton {...props} options={options} onSelect={setContactType} />
      {contactType === MESSAGE_CONTACT_OPTION && (
        <SolidaryMessagesModal
          solidaryId={solidaryId}
          solidarySolutionId={solidarySolutionId}
          onClose={closeModal}
        />
      )}
      {contactType === SMS_CONTACT_OPTION && (
        <SolidarySMSModal
          solidaryId={solidaryId}
          solidarySolutionId={solidarySolutionId}
          onClose={closeModal}
        />
      )}
    </>
  );
};
