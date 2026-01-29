<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Enum;

enum CodePage
{
    case DEFAULT;
    case CP_437; //(USA,Std. Europe)
    case KATAKANA;
    case CP_858; // (Multilingual)
    case CP_852; // (Latin-2)
    case CP_860; // (Portuguese)
    case CP_861; // (Icelandic)
    case CP_863; // (Canadian French)
    case CP_865; // (Nordic)
    case CP_866; // (Cyrillic Russian)
    case CP_855; // (Cyrillic Bulgarian)
    case CP_857; // (Turkey)
    case CP_862; // (Israel (Hebrew) )
    case CP_864; // (Arabic)
    case CP_737; // (Greek)
    case CP_851; // (Greek)
    case CP_869; // (Greek)
    case CP_928; // (Greek)
    case CP_772; // (Lithuanian)
    case CP_774; // (Lithuanian)
    case CP_874; // (Thai)
    case CP_1252; // (Windows Latin-1)
    case CP_1250; // (Windows Latin-2)
    case CP_1251; // (Windows Cyrillic)
    case CP_3840; // (IBM-Russian)
    case CP_3841; // (Gost)
    case CP_3843; // (Polish)
    case CP_3844; // (CS2)
    case CP_3845; // (Hungarian)
    case CP_3846; // (Turkish)
    case CP_3847; // (Brazil-ABNT)
    case CP_3848; // (Brazil-ABICOMP)
    case CP_1001; // (Arabic)
    case CP_2001; // (Lithuanian-KBL)
    case CP_3001; // (Estonian-1)
    case CP_3002; // (Estonian-2)
    case CP_3011; // (Latvian-1)
    case CP_3012; // (Latvian-2)
    case CP_3021; // (Bulgarian)
    case CP_3041; // (Maltese)
    case THAI_CC_42; // (Thai)
    case THAI_CC_11; // (Thai)
    case THAI_CC_13; // (Thai)
    case THAI_CC_18; // (Thai)
    case USER_DEFINED;
}
