import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

import '../theme/app_colors.dart';

class HanovaBrandMark extends StatelessWidget {
  final double size;
  final bool onColor;

  const HanovaBrandMark({super.key, this.size = 52, this.onColor = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      padding: EdgeInsets.all(size * 0.2),
      decoration: BoxDecoration(
        color: onColor
            ? Colors.white.withValues(alpha: 0.16)
            : AppColors.primaryLight,
        borderRadius: BorderRadius.circular(size * 0.32),
        border: Border.all(
          color: onColor
              ? Colors.white.withValues(alpha: 0.2)
              : AppColors.primaryMist,
        ),
      ),
      child: SvgPicture.asset(
        onColor
            ? 'assets/brand/hanova-mark-inverse.svg'
            : 'assets/brand/hanova-mark.svg',
      ),
    );
  }
}

class HanovaAuthShell extends StatelessWidget {
  final String title;
  final String subtitle;
  final Widget child;
  final String? topActionLabel;
  final VoidCallback? onTopAction;
  final bool showBack;

  const HanovaAuthShell({
    super.key,
    required this.title,
    required this.subtitle,
    required this.child,
    this.topActionLabel,
    this.onTopAction,
    this.showBack = true,
  });

  @override
  Widget build(BuildContext context) {
    final keyboardVisible = MediaQuery.viewInsetsOf(context).bottom > 0;

    return Scaffold(
      backgroundColor: AppColors.background,
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: ClipPath(
              clipper: _HanovaWaveClipper(),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 220),
                curve: Curves.easeOutCubic,
                height: keyboardVisible ? 238 : 310,
                decoration: const BoxDecoration(
                  gradient: AppColors.brandGradient,
                ),
                child: Stack(
                  children: [
                    PositionedDirectional(
                      top: -62,
                      end: -54,
                      child: _DecorativeRing(size: 210),
                    ),
                    PositionedDirectional(
                      bottom: 48,
                      start: -50,
                      child: _DecorativeRing(size: 150),
                    ),
                  ],
                ),
              ),
            ),
          ),
          SafeArea(
            child: SingleChildScrollView(
              keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
              padding: EdgeInsets.fromLTRB(
                22,
                keyboardVisible ? 2 : 10,
                22,
                keyboardVisible ? 18 : 32,
              ),
              child: AnimatedSize(
                duration: const Duration(milliseconds: 220),
                curve: Curves.easeOutCubic,
                alignment: Alignment.topCenter,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        if (showBack)
                          _HeaderButton(
                            icon: Icons.arrow_back_ios_new_rounded,
                            onTap: () => context.canPop()
                                ? context.pop()
                                : context.go('/home'),
                          )
                        else
                          const SizedBox(width: 44),
                        const Spacer(),
                        if (topActionLabel != null)
                          TextButton(
                            onPressed: onTopAction,
                            style: TextButton.styleFrom(
                              foregroundColor: Colors.white,
                            ),
                            child: Text(
                              topActionLabel!,
                              style: const TextStyle(
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                      ],
                    ),
                    SizedBox(height: keyboardVisible ? 0 : 10),
                    Center(
                      child: HanovaBrandMark(
                        size: keyboardVisible ? 44 : 58,
                        onColor: true,
                      ),
                    ),
                    SizedBox(height: keyboardVisible ? 6 : 14),
                    Center(
                      child: Text(
                        title,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: keyboardVisible ? 23 : 27,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                    if (!keyboardVisible) ...[
                      const SizedBox(height: 6),
                      Center(
                        child: Text(
                          subtitle,
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.8),
                            fontSize: 13,
                            height: 1.45,
                          ),
                        ),
                      ),
                    ],
                    SizedBox(height: keyboardVisible ? 12 : 50),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.fromLTRB(20, 24, 20, 22),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(28),
                        border: Border.all(color: AppColors.divider),
                        boxShadow: const [
                          BoxShadow(
                            color: AppColors.cardShadow,
                            blurRadius: 26,
                            offset: Offset(0, 12),
                          ),
                        ],
                      ),
                      child: child,
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class HanovaFieldLabel extends StatelessWidget {
  final String text;

  const HanovaFieldLabel(this.text, {super.key});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsetsDirectional.only(start: 4, bottom: 8),
      child: Text(
        text,
        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
      ),
    );
  }
}

class _HeaderButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;

  const _HeaderButton({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white.withValues(alpha: 0.14),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: SizedBox(
          width: 44,
          height: 44,
          child: Icon(icon, color: Colors.white, size: 18),
        ),
      ),
    );
  }
}

class _DecorativeRing extends StatelessWidget {
  final double size;

  const _DecorativeRing({required this.size});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        border: Border.all(
          width: 32,
          color: Colors.white.withValues(alpha: 0.055),
        ),
      ),
    );
  }
}

class _HanovaWaveClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    return Path()
      ..lineTo(0, size.height * 0.78)
      ..cubicTo(
        size.width * 0.2,
        size.height * 1.02,
        size.width * 0.52,
        size.height * 0.68,
        size.width * 0.76,
        size.height * 0.8,
      )
      ..cubicTo(
        size.width * 0.88,
        size.height * 0.86,
        size.width * 0.95,
        size.height * 0.94,
        size.width,
        size.height * 0.9,
      )
      ..lineTo(size.width, 0)
      ..close();
  }

  @override
  bool shouldReclip(covariant CustomClipper<Path> oldClipper) => false;
}
