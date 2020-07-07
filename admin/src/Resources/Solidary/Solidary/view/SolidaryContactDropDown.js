import React, { useState } from 'react';

import DropDownButton from '../../../../components/button/DropDownButton';
import { SolidaryMessagesModal } from '../SolidaryMessagesModal';
import { SolidarySMSModal } from '../SolidarySMSModal';
import { SolidaryFormalResponseModal } from './SolidaryFormalResponseModal';
import { useSolidary } from '../hooks/useSolidary';
import { SOLIDARYASK_STATUS_ASKED } from '../../../../constants/solidaryAskStatus';

const SMS_CONTACT_OPTION = 'Envoyer directement un SMS';
const MESSAGE_CONTACT_OPTION = 'Écrire vers sa messagerie';
const ASKFORRESPONSE_OPTION = 'Solliciter réponse formelle';

const resolveOptions = (solidary, ask) => {
  const options = [SMS_CONTACT_OPTION, MESSAGE_CONTACT_OPTION];
  if (!solidary) {
    return options;
  }

  return [
    ...options,
    ask && ask.status === SOLIDARYASK_STATUS_ASKED && ASKFORRESPONSE_OPTION,
  ].filter((x) => x);
};

export const SolidaryContactDropDown = ({ solidaryId, solidarySolutionId, ...props }) => {
  const [contactType, setContactType] = useState(null);
  const { solidary } = useSolidary(`/solidaries/${solidaryId}`);

  const ask =
    solidary && solidary.asksList.find((i) => i.solidarySolutionId === solidarySolutionId);

  const closeModal = () => setContactType(null);

  return (
    <>
      <DropDownButton
        {...props}
        options={resolveOptions(solidary, ask)}
        onSelect={setContactType}
      />
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
      {contactType === ASKFORRESPONSE_OPTION && (
        <SolidaryFormalResponseModal
          solidaryId={solidaryId}
          solidarySolutionId={solidarySolutionId}
          onClose={closeModal}
        />
      )}
    </>
  );
};
